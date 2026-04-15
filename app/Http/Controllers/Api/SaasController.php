<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libraries\Services\MailService;
use App\Models\Companies;
use App\Models\Companysettings;
use App\Models\Coupon;
use App\Models\Orders;
use App\Models\User;
use App\Models\Compemployees;
use App\Models\Transactions;
use App\Models\Systemsettings;
use App\Support\Tenancy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
class SaasController extends Controller
{
    /**
     * @return array{r:int,g:int,b:int}|null
     */
    private function parseHexRgb(?string $hex): ?array
    {
        if ($hex === null || $hex === '') {
            return null;
        }
        $h = ltrim(trim($hex), '#');
        if (strlen($h) === 3) {
            $h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
        }
        if (!preg_match('/^[a-fA-F0-9]{6}$/', $h)) {
            return null;
        }

        return [
            'r' => hexdec(substr($h, 0, 2)),
            'g' => hexdec(substr($h, 2, 2)),
            'b' => hexdec(substr($h, 4, 2)),
        ];
    }

    private function relativeLuminance(int $r, int $g, int $b): float
    {
        $lin = static function (int $v): float {
            $x = $v / 255;
            return $x <= 0.03928 ? $x / 12.92 : pow(($x + 0.055) / 1.055, 2.4);
        };
        $lr = $lin($r);
        $lg = $lin($g);
        $lb = $lin($b);

        return 0.2126 * $lr + 0.7152 * $lg + 0.0722 * $lb;
    }

    private function normalizeHex(?string $hex): ?string
    {
        $rgb = $this->parseHexRgb($hex);
        if ($rgb === null) {
            return null;
        }

        return sprintf('#%02x%02x%02x', $rgb['r'], $rgb['g'], $rgb['b']);
    }

    /** Match TS `clampAccentForUiTheme`: keep accent readable on light/dark UI. */
    private function clampAccentHex(?string $hex, string $resolvedTheme): string
    {
        $rgb = $this->parseHexRgb($hex);
        if ($rgb === null) {
            return $resolvedTheme === 'dark' ? '#94a3b8' : '#10b981';
        }
        $r = $rgb['r'];
        $g = $rgb['g'];
        $b = $rgb['b'];
        $iter = 28;
        if ($resolvedTheme !== 'dark') {
            for ($i = 0; $i < $iter; $i++) {
                $l = $this->relativeLuminance($r, $g, $b);
                if ($l <= 0.88) {
                    break;
                }
                $r = (int) round($r * 0.8);
                $g = (int) round($g * 0.8);
                $b = (int) round($b * 0.8);
            }
        } else {
            for ($i = 0; $i < $iter; $i++) {
                $l = $this->relativeLuminance($r, $g, $b);
                if ($l >= 0.12 && $l <= 0.88) {
                    break;
                }
                if ($l < 0.12) {
                    $r = (int) round($r + (255 - $r) * 0.22);
                    $g = (int) round($g + (255 - $g) * 0.22);
                    $b = (int) round($b + (255 - $b) * 0.22);
                } else {
                    $r = (int) round($r * 0.82);
                    $g = (int) round($g * 0.82);
                    $b = (int) round($b * 0.82);
                }
            }
        }

        return sprintf(
            '#%02x%02x%02x',
            max(0, min(255, $r)),
            max(0, min(255, $g)),
            max(0, min(255, $b)),
        );
    }

    private function dashboardRange(Request $request): array
    {
        $key = (string) $request->query('range', '30d');
        $now = now();

        if ($key === '90d') {
            $currFrom = $now->copy()->subDays(90);
            $prevFrom = $now->copy()->subDays(180);
            $prevTo = $now->copy()->subDays(90);
            $months = 6;
        } elseif ($key === 'ytd') {
            $currFrom = $now->copy()->startOfYear();
            $prevFrom = $currFrom->copy()->subYear();
            $prevTo = $currFrom->copy();
            $months = 12;
        } else {
            // default: 30d
            $currFrom = $now->copy()->subDays(30);
            $prevFrom = $now->copy()->subDays(60);
            $prevTo = $now->copy()->subDays(30);
            $months = 6;
            $key = '30d';
        }

        return [$key, $now, $currFrom, $prevFrom, $prevTo, $months];
    }

    private function dashboardStatus(Request $request): string
    {
        $s = (string) $request->query('status', 'all');
        return in_array($s, ['all', 'in_progress', 'completed'], true) ? $s : 'all';
    }

    private function isAdminUser(): bool
    {
        $u = Auth::user();
        if (!$u) return false;

        $role = strtolower((string) ($u->role ?? ''));
        if (in_array($role, ['admin', 'superadmin', 'super_admin', 'platform', 'rr', 'root'], true)) {
            return true;
        }

        $rrCompanyId = (int) env('RR_COMPANY_ID');
        if ($rrCompanyId > 0 && (int) ($u->company_id ?? 0) === $rrCompanyId) {
            return true;
        }

        return false;
    }

    /** Matches classic sidebar: RR company account only (`company_id == RR_COMPANY_ID`). */
    private function actsAsRrCompany(): bool
    {
        $rr = (int) env('RR_COMPANY_ID');
        if ($rr <= 0) {
            return false;
        }
        $cid = $this->effectiveCompanyId();

        return $cid !== null && (int) $cid === $rr;
    }

    private function effectiveCompanyId(): ?int
    {
        // Prefer the logged-in user's company_id; fall back to middleware-bound companySettings.
        $u = Auth::user();
        if ($u && $u->company_id !== null && $u->company_id !== '') {
            return (int) $u->company_id;
        }
        $settings = app('companySettings');
        return $settings?->company_id ? (int) $settings->company_id : null;
    }

    public function me(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $rr = (int) env('RR_COMPANY_ID');

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'company_id' => $user->company_id,
            'is_rr_company' => $rr > 0 && (int) ($user->company_id ?? 0) === $rr,
        ]);
    }

    public function dashboard(Request $request)
    {
        $companyId = $this->effectiveCompanyId();
        $isAdmin = $this->isAdminUser();
        $rrCompanyId = (int) env('RR_COMPANY_ID');
        if ($rrCompanyId <= 0 && $isAdmin) {
            $rrCompanyId = (int) ($companyId ?? 0);
        }

        [, $now, $currFrom, $prevFrom, $prevTo] = $this->dashboardRange($request);
        $status = $this->dashboardStatus($request);

        $pct = function (float|int $curr, float|int $prev): ?float {
            $curr = (float) $curr;
            $prev = (float) $prev;
            if ($prev == 0.0) {
                return $curr > 0 ? 100.0 : null;
            }
            return (($curr - $prev) / $prev) * 100.0;
        };

        // Match legacy HomeController::dashboard counts + paid total (DB has real data shaped for these queries).
        // Admin users see all-company aggregates (unscoped).
        if ($isAdmin || ((int) $companyId === (int) $rrCompanyId)) {
            $inProgress = Compemployees::query()
                ->where('compemployees.soft_del', '=', 0)
                ->where(function ($q) {
                    $q->where('compemployees.receive_label_status', '!=', 'DELIVERED')
                        ->orWhere(function ($subQuery) {
                            $subQuery->where('compemployees.dest_flag', '=', 1)
                                ->where('compemployees.dest_label_status', '!=', 'DELIVERED');
                        });
                })
                ->count();

            $completed = Compemployees::query()
                ->where('compemployees.soft_del', '=', 0)
                ->where(function ($q) {
                    $q->where('compemployees.receive_label_status', '=', 'DELIVERED')
                        ->orWhere(function ($subQuery) {
                            $subQuery->where('compemployees.dest_flag', '=', 1)
                                ->where('compemployees.receive_label_status', '=', 'DELIVERED')
                                ->where('compemployees.dest_label_status', '=', 'DELIVERED');
                        });
                })
                ->count();

            $totalPaid = (float) Transactions::query()
                ->whereRaw('LOWER(TRIM(transactions.status)) = ?', ['success'])
                ->sum('transactions.amount');

            $inProgressCurr = Compemployees::query()
                ->where('compemployees.soft_del', 0)
                ->whereBetween('compemployees.created_at', [$currFrom, $now])
                ->where(function ($q) {
                    $q->where('compemployees.receive_label_status', '!=', 'DELIVERED')
                        ->orWhere(function ($subQuery) {
                            $subQuery->where('compemployees.dest_flag', 1)
                                ->where('compemployees.dest_label_status', '!=', 'DELIVERED');
                        });
                })
                ->count();
            $inProgressPrev = Compemployees::query()
                ->where('compemployees.soft_del', 0)
                ->whereBetween('compemployees.created_at', [$prevFrom, $prevTo])
                ->where(function ($q) {
                    $q->where('compemployees.receive_label_status', '!=', 'DELIVERED')
                        ->orWhere(function ($subQuery) {
                            $subQuery->where('compemployees.dest_flag', 1)
                                ->where('compemployees.dest_label_status', '!=', 'DELIVERED');
                        });
                })
                ->count();

            $completedCurr = Compemployees::query()
                ->where('compemployees.soft_del', 0)
                ->whereBetween('compemployees.created_at', [$currFrom, $now])
                ->where(function ($q) {
                    $q->where('compemployees.receive_label_status', '=', 'DELIVERED')
                        ->orWhere(function ($subQuery) {
                            $subQuery->where('compemployees.dest_flag', 1)
                                ->where('compemployees.receive_label_status', '=', 'DELIVERED')
                                ->where('compemployees.dest_label_status', '=', 'DELIVERED');
                        });
                })
                ->count();
            $completedPrev = Compemployees::query()
                ->where('compemployees.soft_del', 0)
                ->whereBetween('compemployees.created_at', [$prevFrom, $prevTo])
                ->where(function ($q) {
                    $q->where('compemployees.receive_label_status', '=', 'DELIVERED')
                        ->orWhere(function ($subQuery) {
                            $subQuery->where('compemployees.dest_flag', 1)
                                ->where('compemployees.receive_label_status', '=', 'DELIVERED')
                                ->where('compemployees.dest_label_status', '=', 'DELIVERED');
                        });
                })
                ->count();

            $paidCurr = (float) Transactions::query()
                ->whereRaw('LOWER(TRIM(transactions.status)) = ?', ['success'])
                ->whereBetween('transactions.created_at', [$currFrom, $now])
                ->sum('transactions.amount');
            $paidPrev = (float) Transactions::query()
                ->whereRaw('LOWER(TRIM(transactions.status)) = ?', ['success'])
                ->whereBetween('transactions.created_at', [$prevFrom, $prevTo])
                ->sum('transactions.amount');
        } else {
            $inProgress = Compemployees::query()
                ->where('parent_comp_id', $companyId)
                ->where('compemployees.soft_del', '=', 0)
                ->where(function ($q) use ($companyId) {
                    $q->where('compemployees.receive_label_status', '!=', 'DELIVERED')
                        ->orWhere(function ($subQuery) use ($companyId) {
                            $subQuery->where('compemployees.dest_flag', '=', 1)
                                ->where('compemployees.dest_label_status', '!=', 'DELIVERED')
                                ->where('compemployees.parent_comp_id', '=', $companyId);
                        });
                })
                ->count();

            $completed = Compemployees::query()
                ->where('parent_comp_id', $companyId)
                ->where('compemployees.soft_del', '=', 0)
                ->where(function ($q) use ($companyId) {
                    $q->where('compemployees.receive_label_status', '=', 'DELIVERED')
                        ->orWhere(function ($subQuery) use ($companyId) {
                            $subQuery->where('compemployees.dest_flag', '=', 1)
                                ->where('compemployees.receive_label_status', '=', 'DELIVERED')
                                ->where('compemployees.dest_label_status', '=', 'DELIVERED')
                                ->where('compemployees.parent_comp_id', '=', $companyId);
                        });
                })
                ->count();

            $totalPaid = (float) Transactions::query()
                ->whereRaw('LOWER(TRIM(transactions.status)) = ?', ['success'])
                ->where('transactions.company_id', $companyId)
                ->sum('transactions.amount');

            $inProgressCurr = Compemployees::query()
                ->where('parent_comp_id', $companyId)
                ->where('compemployees.soft_del', 0)
                ->whereBetween('compemployees.created_at', [$currFrom, $now])
                ->where(function ($q) use ($companyId) {
                    $q->where('compemployees.receive_label_status', '!=', 'DELIVERED')
                        ->orWhere(function ($subQuery) use ($companyId) {
                            $subQuery->where('compemployees.dest_flag', 1)
                                ->where('compemployees.dest_label_status', '!=', 'DELIVERED')
                                ->where('compemployees.parent_comp_id', $companyId);
                        });
                })
                ->count();
            $inProgressPrev = Compemployees::query()
                ->where('parent_comp_id', $companyId)
                ->where('compemployees.soft_del', 0)
                ->whereBetween('compemployees.created_at', [$prevFrom, $prevTo])
                ->where(function ($q) use ($companyId) {
                    $q->where('compemployees.receive_label_status', '!=', 'DELIVERED')
                        ->orWhere(function ($subQuery) use ($companyId) {
                            $subQuery->where('compemployees.dest_flag', 1)
                                ->where('compemployees.dest_label_status', '!=', 'DELIVERED')
                                ->where('compemployees.parent_comp_id', $companyId);
                        });
                })
                ->count();

            $completedCurr = Compemployees::query()
                ->where('parent_comp_id', $companyId)
                ->where('compemployees.soft_del', 0)
                ->whereBetween('compemployees.created_at', [$currFrom, $now])
                ->where(function ($q) use ($companyId) {
                    $q->where('compemployees.receive_label_status', '=', 'DELIVERED')
                        ->orWhere(function ($subQuery) use ($companyId) {
                            $subQuery->where('compemployees.dest_flag', 1)
                                ->where('compemployees.receive_label_status', '=', 'DELIVERED')
                                ->where('compemployees.dest_label_status', '=', 'DELIVERED')
                                ->where('compemployees.parent_comp_id', $companyId);
                        });
                })
                ->count();
            $completedPrev = Compemployees::query()
                ->where('parent_comp_id', $companyId)
                ->where('compemployees.soft_del', 0)
                ->whereBetween('compemployees.created_at', [$prevFrom, $prevTo])
                ->where(function ($q) use ($companyId) {
                    $q->where('compemployees.receive_label_status', '=', 'DELIVERED')
                        ->orWhere(function ($subQuery) use ($companyId) {
                            $subQuery->where('compemployees.dest_flag', 1)
                                ->where('compemployees.receive_label_status', '=', 'DELIVERED')
                                ->where('compemployees.dest_label_status', '=', 'DELIVERED')
                                ->where('compemployees.parent_comp_id', $companyId);
                        });
                })
                ->count();

            $paidCurr = (float) Transactions::query()
                ->whereRaw('LOWER(TRIM(transactions.status)) = ?', ['success'])
                ->where('transactions.company_id', $companyId)
                ->whereBetween('transactions.created_at', [$currFrom, $now])
                ->sum('transactions.amount');
            $paidPrev = (float) Transactions::query()
                ->whereRaw('LOWER(TRIM(transactions.status)) = ?', ['success'])
                ->where('transactions.company_id', $companyId)
                ->whereBetween('transactions.created_at', [$prevFrom, $prevTo])
                ->sum('transactions.amount');
        }

        $recentQuery = DB::table('compemployees')
            ->leftJoin('orders', 'compemployees.order_id', '=', 'orders.id')
            ->select(
                'compemployees.id as item_id',
                'compemployees.order_id as order_id',
                'compemployees.type_of_equip as equip_type',
                'compemployees.receive_label_status as label_status',
                'compemployees.created_at as created_at',
                'compemployees.order_amt as amount'
            )
            ->where('compemployees.soft_del', 0)
            ->whereBetween('compemployees.created_at', [$currFrom, $now])
            ->when($status === 'completed', fn ($q) => $q->where('compemployees.receive_label_status', '=', 'DELIVERED'))
            ->when($status === 'in_progress', fn ($q) => $q->where('compemployees.receive_label_status', '!=', 'DELIVERED'))
            ->when(
                !$isAdmin && $companyId && (int) $companyId !== (int) $rrCompanyId,
                fn ($q) => $q->where('compemployees.parent_comp_id', $companyId)
            );

        $recent = $recentQuery->orderByDesc('compemployees.id')->limit(10)->get();

        return response()
            ->json([
                'kpis' => [
                    'inProgress' => $inProgress,
                    'completed' => $completed,
                    'totalPaid' => $totalPaid,
                    'trends' => [
                        'inProgressPct' => $pct($inProgressCurr ?? 0, $inProgressPrev ?? 0),
                        'completedPct' => $pct($completedCurr ?? 0, $completedPrev ?? 0),
                        'totalPaidPct' => $pct($paidCurr ?? 0, $paidPrev ?? 0),
                    ],
                ],
                'recentShipments' => $recent,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function dashboardCharts(Request $request)
    {
        $companyId = $this->effectiveCompanyId();
        $isAdmin = $this->isAdminUser();
        $rrCompanyId = (int) env('RR_COMPANY_ID');
        if ($rrCompanyId <= 0 && $isAdmin) {
            $rrCompanyId = (int) ($companyId ?? 0);
        }
        [, $now, $currFrom, , ,] = $this->dashboardRange($request);
        $status = $this->dashboardStatus($request);
        // Align trend data with the same window as KPIs / table (30d, 90d, YTD) — do not always show 6 rolling calendar months.
        $since = $currFrom->copy();
        $scopeCompany = !$isAdmin && $companyId && (int) $companyId !== (int) $rrCompanyId;

        $txScope = DB::table('transactions')
            ->whereRaw('LOWER(TRIM(transactions.status)) = ?', ['success'])
            ->where('transactions.created_at', '>=', $since)
            ->when($scopeCompany, fn ($q) => $q->where('transactions.company_id', $companyId));

        // Revenue: one row per transaction (avoid duplicating amount via compemployees join).
        $revByMonth = (clone $txScope)
            ->selectRaw("DATE_FORMAT(transactions.created_at, '%Y-%m') as ym, SUM(transactions.amount) as revenue")
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

        // Margin series = commission (partner equipment price − RR base) per line item, attributed to payment month.
        $marginByMonth = DB::table('transactions')
            ->join('compemployees', function ($join) {
                $join->on('compemployees.order_id', '=', 'transactions.order_id')
                    ->where('compemployees.soft_del', '=', 0);
            })
            ->leftJoin('systemsettings as ss_rr', function ($join) use ($rrCompanyId) {
                $join->on('ss_rr.equipment_type', '=', 'compemployees.type_of_equip')
                    ->where('ss_rr.company_id', '=', $rrCompanyId);
            })
            ->leftJoin('systemsettings as ss_co', function ($join) {
                $join->on('ss_co.equipment_type', '=', 'compemployees.type_of_equip')
                    ->on('ss_co.company_id', '=', 'compemployees.company_id');
            })
            ->whereRaw('LOWER(TRIM(transactions.status)) = ?', ['success'])
            ->where('transactions.created_at', '>=', $since)
            ->when($scopeCompany, fn ($q) => $q->where('transactions.company_id', $companyId))
            ->selectRaw(
                "DATE_FORMAT(transactions.created_at, '%Y-%m') as ym, "
                .'SUM(CASE WHEN ss_co.id IS NOT NULL AND ss_rr.id IS NOT NULL '
                .'THEN (ss_co.order_amount - ss_rr.order_amount) ELSE 0 END) as margin'
            )
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

        $labels = [];
        $revenue = [];
        $margin = [];
        $startMonth = $currFrom->copy()->startOfMonth();
        $endMonth = $now->copy()->startOfMonth();
        for ($cursor = $startMonth->copy(); $cursor->lte($endMonth); $cursor->addMonth()) {
            $ym = $cursor->format('Y-m');
            $labels[] = $ym;
            $revenue[] = (float) ($revByMonth[$ym]->revenue ?? 0);
            $margin[] = (float) ($marginByMonth[$ym]->margin ?? 0);
        }

        $equipQuery = DB::table('compemployees')
            ->select('type_of_equip', DB::raw('COUNT(*) as cnt'))
            ->where('soft_del', 0)
            ->where('compemployees.created_at', '>=', $currFrom)
            ->when($status === 'completed', fn ($q) => $q->where('receive_label_status', '=', 'DELIVERED'))
            ->when($status === 'in_progress', fn ($q) => $q->where('receive_label_status', '!=', 'DELIVERED'))
            ->when($scopeCompany, fn ($q) => $q->where('parent_comp_id', $companyId));

        $equip = $equipQuery
            ->groupBy('type_of_equip')
            ->orderByDesc('cnt')
            ->limit(8)
            ->get()
            ->values();

        // Optional: shipment status mix for the same doughnut-style chart (DB-backed).
        $statusMix = DB::table('compemployees')
            ->select('receive_label_status', DB::raw('COUNT(*) as cnt'))
            ->where('soft_del', 0)
            ->where('compemployees.created_at', '>=', $currFrom)
            ->when($status === 'completed', fn ($q) => $q->where('receive_label_status', '=', 'DELIVERED'))
            ->when($status === 'in_progress', fn ($q) => $q->where('receive_label_status', '!=', 'DELIVERED'))
            ->when($scopeCompany, fn ($q) => $q->where('parent_comp_id', $companyId))
            ->groupBy('receive_label_status')
            ->orderByDesc('cnt')
            ->limit(10)
            ->get()
            ->values();

        return response()
            ->json([
                'trends' => [
                    'labels' => $labels,
                    'revenue' => $revenue,
                    'margin' => $margin,
                ],
                'equipmentDistribution' => $equip,
                'statusDistribution' => $statusMix,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function dashboardExport(Request $request)
    {
        $companyId = $this->effectiveCompanyId();
        $isAdmin = $this->isAdminUser();
        $rrCompanyId = (int) env('RR_COMPANY_ID');
        if ($rrCompanyId <= 0 && $isAdmin) {
            $rrCompanyId = (int) ($companyId ?? 0);
        }

        [, $now, $currFrom] = $this->dashboardRange($request);
        $status = $this->dashboardStatus($request);

        $scopeCompany = !$isAdmin && $companyId && (int) $companyId !== (int) $rrCompanyId;

        $q = DB::table('compemployees')
            ->select(
                'compemployees.id as item_id',
                'compemployees.order_id as order_id',
                'compemployees.type_of_equip as equip_type',
                'compemployees.receive_label_status as status',
                'compemployees.created_at as created_at',
                'compemployees.order_amt as amount'
            )
            ->where('compemployees.soft_del', 0)
            ->whereBetween('compemployees.created_at', [$currFrom, $now])
            ->when($status === 'completed', fn ($q) => $q->where('compemployees.receive_label_status', '=', 'DELIVERED'))
            ->when($status === 'in_progress', fn ($q) => $q->where('compemployees.receive_label_status', '!=', 'DELIVERED'))
            ->when($scopeCompany, fn ($q) => $q->where('compemployees.parent_comp_id', $companyId))
            ->orderByDesc('compemployees.id');

        $filename = 'dashboard-export-' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($q) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['item_id', 'order_id', 'equip_type', 'status', 'created_at', 'amount']);
            foreach ($q->cursor() as $row) {
                fputcsv($out, [
                    $row->item_id,
                    $row->order_id,
                    $row->equip_type,
                    $row->status,
                    $row->created_at,
                    $row->amount,
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function commissions(Request $request)
    {
        // Same RR-vs-tenant rule as before, but use effectiveCompanyId() so KPIs match the dashboard
        // (middleware `companySettings` alone can disagree with the authenticated user's company).
        $companyId = $this->effectiveCompanyId();
        $rrCompanyId = (int) env('RR_COMPANY_ID');

        $orderAmount = DB::table('compemployees')
            ->where('soft_del', 0)
            ->where('send_flag', 1)
            ->where('rec_flag', 1)
            ->when(
                $companyId && (int) $companyId !== $rrCompanyId,
                fn ($q) => $q->where('company_id', $companyId)
            )
            ->select('company_id', 'type_of_equip')
            ->get();

        $deviceAmount = 0;
        $commissionAmount = 0;

        foreach ($orderAmount as $value) {
            $rr = Systemsettings::where('company_id', $rrCompanyId)
                ->where('equipment_type', $value->type_of_equip)
                ->first();
            $company = Systemsettings::where('company_id', $value->company_id)
                ->where('equipment_type', $value->type_of_equip)
                ->first();

            if ($rr && $company) {
                $deviceAmount += (int) $rr->order_amount;
                $commissionAmount += ((int) $company->order_amount - (int) $rr->order_amount);
            }
        }

        return response()->json([
            'deviceAmount' => $deviceAmount,
            'commissionAmount' => $commissionAmount,
        ]);
    }

    /**
     * Rows for the SaaS commissions table (static `frontend saas/pages/commissions.html`).
     */
    public function commissionPartners(Request $request)
    {
        if (! $this->actsAsRrCompany()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $settings = app('companySettings');
        $companyId = $settings?->company_id;
        $rrCompanyId = env('RR_COMPANY_ID');

        if (!$companyId) {
            return response()->json(['data' => []]);
        }

        $agg = json_decode($this->commissions($request)->getContent(), true);

        if ((int) $companyId !== (int) $rrCompanyId) {
            $name = Companies::query()->where('id', $companyId)->value('company_name');

            return response()->json([
                'data' => [[
                    'partner' => $name ?? 'Partner',
                    'rate' => '—',
                    'total_earned' => (float) ($agg['commissionAmount'] ?? 0),
                    'last_payout' => null,
                ]],
            ]);
        }

        $partners = Companies::query()
            ->where('parent_company', $companyId)
            ->orderBy('company_name')
            ->get(['id', 'company_name']);

        $data = [];
        foreach ($partners as $p) {
            $data[] = [
                'partner' => $p->company_name,
                'rate' => '—',
                'total_earned' => null,
                'last_payout' => null,
            ];
        }

        return response()->json(['data' => $data]);
    }

    public function prices(Request $request)
    {
        $settings = app('companySettings');
        $companyId = $settings?->company_id;
        $rrCompanyId = (int) env('RR_COMPANY_ID');

        $rows = Systemsettings::query()
            ->where('company_id', $companyId)
            ->orderBy('equipment_type')
            ->get(['id', 'equipment_type', 'order_amount', 'company_id']);

        $adminFloorByType = [];
        if ($rrCompanyId > 0 && (int) $companyId !== $rrCompanyId) {
            $adminFloorByType = Systemsettings::query()
                ->where('company_id', $rrCompanyId)
                ->pluck('order_amount', 'equipment_type')
                ->map(fn ($v) => (float) $v)
                ->toArray();
        }

        $out = $rows->map(function ($r) use ($adminFloorByType) {
            $type = $r->equipment_type;

            return [
                'id' => $r->id,
                'equipment_type' => $r->equipment_type,
                'order_amount' => $r->order_amount,
                'company_id' => $r->company_id,
                'min_order_amount' => $adminFloorByType[$type] ?? null,
            ];
        });

        return response()->json($out);
    }

    public function updatePrices(Request $request)
    {
        $settings = app('companySettings');
        $companyId = $settings?->company_id;
        $rrCompanyId = (int) env('RR_COMPANY_ID');

        $data = $request->validate([
            'prices' => ['required', 'array'],
            'prices.*.equipment_type' => ['required', 'string'],
            'prices.*.order_amount' => ['required', 'numeric'],
        ]);

        if ($rrCompanyId > 0 && (int) $companyId !== $rrCompanyId) {
            $adminFloorByType = Systemsettings::query()
                ->where('company_id', $rrCompanyId)
                ->pluck('order_amount', 'equipment_type')
                ->map(fn ($v) => (float) $v)
                ->toArray();

            foreach ($data['prices'] as $index => $p) {
                $equipmentType = (string) $p['equipment_type'];
                $requestedAmount = (float) $p['order_amount'];
                if (!array_key_exists($equipmentType, $adminFloorByType)) {
                    continue;
                }
                $adminFloor = (float) $adminFloorByType[$equipmentType];
                if ($requestedAmount < $adminFloor) {
                    $adminFloorText = number_format($adminFloor, 2, '.', '');
                    $specificMessage = "Base fee for {$equipmentType} cannot be lower than admin price \${$adminFloorText}.";
                    return response()->json([
                        'message' => $specificMessage,
                        'errors' => [
                            "prices.$index.order_amount" => [$specificMessage],
                        ],
                    ], 422);
                }
            }
        }

        foreach ($data['prices'] as $p) {
            Systemsettings::updateOrCreate(
                ['company_id' => $companyId, 'equipment_type' => $p['equipment_type']],
                ['order_amount' => (int) $p['order_amount']]
            );
        }

        return $this->prices($request);
    }

    public function orderDetail(Request $request, int $itemId)
    {
        $settings = app('companySettings');
        $companyId = $settings?->company_id;

        $row = DB::table('compemployees')
            ->leftJoin('orders', 'compemployees.order_id', '=', 'orders.id')
            ->leftJoin('companies', 'compemployees.company_id', '=', 'companies.id')
            ->leftJoin('users', 'compemployees.user_id', '=', 'users.id')
            ->where('compemployees.id', $itemId)
            ->when($companyId && $companyId != env('RR_COMPANY_ID'), fn ($q) => $q->where('compemployees.parent_comp_id', $companyId))
            ->select(
                'compemployees.*',
                'orders.status as order_status',
                'companies.company_name as company_name',
                'users.email as user_email'
            )
            ->first();

        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($row);
    }

    public function createOrder(Request $request)
    {
        $settings = app('companySettings');
        $companyId = $settings?->company_id;

        $data = $request->validate([
            'emp_first_name' => ['nullable', 'string'],
            'emp_last_name' => ['nullable', 'string'],
            'emp_email' => ['nullable', 'string'],
            'emp_phone' => ['nullable', 'string'],
            'emp_add_1' => ['nullable', 'string'],
            'emp_city' => ['nullable', 'string'],
            'emp_state' => ['nullable', 'string'],
            'emp_pcode' => ['nullable', 'string'],
            'type_of_equip' => ['required', 'string'],
            'return_service' => ['required', 'string'],
            'receipient_name' => ['nullable', 'string'],
            'receipient_email' => ['nullable', 'string'],
            'receipient_phone' => ['nullable', 'string'],
            'receipient_add_1' => ['nullable', 'string'],
            'receipient_city' => ['nullable', 'string'],
            'receipient_state' => ['nullable', 'string'],
            'receipient_zip' => ['nullable', 'string'],
            'custom_msg' => ['nullable', 'string'],
        ]);

        $order = Orders::create([
            'company_id' => $companyId,
            'status' => 'pending',
        ]);

        $sub = Compemployees::create(array_merge($data, [
            'company_id' => $companyId,
            'parent_comp_id' => $companyId,
            'user_id' => Auth::id(),
            'order_id' => $order->id,
            'send_flag' => 1,
            'rec_flag' => 1,
        ]));

        return response()->json([
            'order_id' => $order->id,
            'item_id' => $sub->id,
        ], 201);
    }

    public function settings(Request $request)
    {
        $settings = app('companySettings');
        $row = Companysettings::query()
            ->with('company:id,company_name')
            ->find($settings->id);

        return response()->json($row);
    }

    public function updateSettings(Request $request)
    {
        $settings = app('companySettings');

        $data = $request->validate([
            'btn_bg_color' => ['nullable', 'string'],
            'btn_font_color' => ['nullable', 'string'],
            'theme_bg_color' => ['nullable', 'string'],
            'theme_font_color' => ['nullable', 'string'],
            'settings_data' => ['nullable'],
        ]);

        $row = Companysettings::query()->findOrFail($settings->id);

        if (array_key_exists('settings_data', $data) && is_array($data['settings_data'])) {
            $existing = $row->settings_data;
            if (is_string($existing)) {
                $decoded = json_decode($existing, true);
                $existing = is_array($decoded) ? $decoded : [];
            }
            if (!is_array($existing)) {
                $existing = [];
            }
            $data['settings_data'] = array_replace_recursive($existing, $data['settings_data']);
        }

        $mergedSd = [];
        if (array_key_exists('settings_data', $data) && is_array($data['settings_data'])) {
            $mergedSd = $data['settings_data'];
        } else {
            $raw = $row->settings_data;
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                $mergedSd = is_array($decoded) ? $decoded : [];
            } elseif (is_array($raw)) {
                $mergedSd = $raw;
            }
        }
        $resolvedTheme = (($mergedSd['theme'] ?? 'light') === 'dark') ? 'dark' : 'light';

        $userHex = null;
        if (isset($mergedSd['primaryColorUser']) && is_string($mergedSd['primaryColorUser']) && $mergedSd['primaryColorUser'] !== '') {
            $userHex = $mergedSd['primaryColorUser'];
        } elseif (isset($data['btn_bg_color']) && is_string($data['btn_bg_color']) && $data['btn_bg_color'] !== '') {
            $userHex = $data['btn_bg_color'];
        } elseif (isset($mergedSd['primaryColor']) && is_string($mergedSd['primaryColor']) && $mergedSd['primaryColor'] !== '') {
            $userHex = $mergedSd['primaryColor'];
        } elseif (is_string($row->btn_bg_color) && $row->btn_bg_color !== '') {
            $userHex = $row->btn_bg_color;
        }

        $normUser = $this->normalizeHex($userHex);
        $effective = $this->clampAccentHex($normUser ?? $userHex, $resolvedTheme);
        $storeUser = $normUser ?? (is_string($userHex) ? trim($userHex) : $effective);

        $mergedSd['primaryColor'] = $effective;
        $mergedSd['primaryColorUser'] = $storeUser;
        $data['btn_bg_color'] = $effective;
        $data['settings_data'] = $mergedSd;

        $row->fill($data);
        $row->save();

        return response()->json($row->load('company:id,company_name'));
    }

    public function uploadLogo(Request $request)
    {
        $settings = app('companySettings');
        $companyId = (int) ($settings?->company_id ?? 0);
        if ($companyId <= 0) {
            return response()->json(['message' => 'Company not configured.'], 400);
        }

        $data = $request->validate([
            'logo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $file = $data['logo'];
        $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
        $filename = 'company-' . $companyId . '-' . Str::uuid()->toString() . '.' . $ext;
        $file->storeAs('logoImage', $filename, 'public');

        $row = Companysettings::query()->findOrFail($settings->id);
        $row->logo = $filename;
        $row->save();

        return response()->json([
            'logo' => $filename,
            'logoUrl' => asset('storage/logoImage/' . $filename),
        ]);
    }

    public function orders(Request $request)
    {
        $request->validate([
            'status' => ['required', 'in:in_progress,completed'],
        ]);

        $labelStatus = strtolower((string) $request->query('label_status', 'all'));
        if (!in_array($labelStatus, ['all', 'label', 'in_transit'], true)) {
            $labelStatus = 'all';
        }

        $perPage = (int) (env('PER_PAGE_DATA') ?: 15);
        $settings = app('companySettings');

        if ($request->query('status') === 'completed') {
            $query = DB::table('compemployees')
                ->leftJoin('companies', 'compemployees.company_id', '=', 'companies.id')
                ->leftJoin('orders', 'compemployees.order_id', '=', 'orders.id')
                ->leftJoin('users', 'compemployees.user_id', '=', 'users.id')
                ->select(
                    'compemployees.id as item_id',
                    'compemployees.type_of_equip as equip_type',
                    'compemployees.return_service as return_srv',
                    'compemployees.order_id as order_id',
                    'companies.company_name as company_name',
                    'compemployees.created_at as order_createdAt',
                    'compemployees.receipient_name as customer_name',
                    'compemployees.receive_label_status as receive_label_status',
                    'compemployees.order_amt as final_amount',
                    'compemployees.updated_at as completed_at',
                    'orders.status as order_status'
                )
                ->where('compemployees.receive_label_status', 'DELIVERED');
        } else {
            $query = DB::table('compemployees')
                ->leftJoin('companies', 'compemployees.company_id', '=', 'companies.id')
                ->leftJoin('orders', 'compemployees.order_id', '=', 'orders.id')
                ->leftJoin('users', 'compemployees.user_id', '=', 'users.id')
                ->leftJoin('companies as order_parent_company', 'compemployees.parent_comp_id', '=', 'order_parent_company.id')
                ->select(
                    'compemployees.id as item_id',
                    'compemployees.type_of_equip as equip_type',
                    'compemployees.return_service as return_srv',
                    'compemployees.order_id as order_id',
                    'companies.company_name as company_name',
                    'compemployees.created_at as order_createdAt',
                    'compemployees.receipient_name as company',
                    'compemployees.receipient_name as customer_name',
                    'compemployees.send_label_status as send_label_status',
                    'compemployees.receive_label_status as receive_label_status',
                    'orders.status as payStatus',
                    'orders.status as order_status'
                )
                ->where('compemployees.receive_label_status', '!=', 'DELIVERED');
        }

        if ($settings && $settings->company_id != env('RR_COMPANY_ID')) {
            $query->where('compemployees.parent_comp_id', $settings->company_id);
        }

        $query->where('compemployees.soft_del', 0);

        // Optional client-side filter for the "In Progress" page.
        // Note: DB fields don't map 1:1 to pills, so keep it simple.
        if ($request->query('status') === 'in_progress' && $labelStatus !== 'all') {
            if ($labelStatus === 'label') {
                $query->where(function ($q) {
                    $q->whereNull('compemployees.send_label_status')
                        ->orWhereRaw('TRIM(compemployees.send_label_status) = ?', ['']);
                });
            } elseif ($labelStatus === 'in_transit') {
                $query->where(function ($q) {
                    $q->whereNotNull('compemployees.send_label_status')
                        ->whereRaw('TRIM(compemployees.send_label_status) != ?', ['']);
                });
            }
        }

        return response()->json($query->orderBy('compemployees.id', 'desc')->paginate($perPage));
    }

    public function ordersExport(Request $request)
    {
        $request->validate([
            'status' => ['required', 'in:in_progress,completed'],
        ]);

        $status = (string) $request->query('status');
        $labelStatus = strtolower((string) $request->query('label_status', 'all'));
        if (!in_array($labelStatus, ['all', 'label', 'in_transit'], true)) {
            $labelStatus = 'all';
        }

        $settings = app('companySettings');

        if ($status === 'completed') {
            $query = DB::table('compemployees')
                ->leftJoin('orders', 'compemployees.order_id', '=', 'orders.id')
                ->select(
                    'compemployees.id as item_id',
                    'compemployees.order_id as order_id',
                    'compemployees.type_of_equip as equip_type',
                    'compemployees.receive_label_status as receive_label_status',
                    'compemployees.created_at as order_createdAt',
                    'orders.status as payStatus'
                )
                ->where('compemployees.receive_label_status', 'DELIVERED');
        } else {
            $query = DB::table('compemployees')
                ->leftJoin('orders', 'compemployees.order_id', '=', 'orders.id')
                ->select(
                    'compemployees.id as item_id',
                    'compemployees.order_id as order_id',
                    'compemployees.type_of_equip as equip_type',
                    'compemployees.send_label_status as send_label_status',
                    'compemployees.receive_label_status as receive_label_status',
                    'compemployees.created_at as order_createdAt',
                    'orders.status as payStatus'
                )
                ->where('compemployees.receive_label_status', '!=', 'DELIVERED');
        }

        if ($settings && $settings->company_id != env('RR_COMPANY_ID')) {
            $query->where('compemployees.parent_comp_id', $settings->company_id);
        }
        $query->where('compemployees.soft_del', 0);

        if ($status === 'in_progress' && $labelStatus !== 'all') {
            if ($labelStatus === 'label') {
                $query->where(function ($q) {
                    $q->whereNull('compemployees.send_label_status')
                        ->orWhereRaw('TRIM(compemployees.send_label_status) = ?', ['']);
                });
            } elseif ($labelStatus === 'in_transit') {
                $query->where(function ($q) {
                    $q->whereNotNull('compemployees.send_label_status')
                        ->whereRaw('TRIM(compemployees.send_label_status) != ?', ['']);
                });
            }
        }

        $filename = $status . '-orders-' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['item_id', 'order_id', 'equip_type', 'payStatus', 'send_label_status', 'receive_label_status', 'order_createdAt']);
            foreach ($query->orderBy('compemployees.id', 'desc')->cursor() as $row) {
                fputcsv($out, [
                    $row->item_id ?? null,
                    $row->order_id ?? null,
                    $row->equip_type ?? null,
                    $row->payStatus ?? null,
                    $row->send_label_status ?? null,
                    $row->receive_label_status ?? null,
                    $row->order_createdAt ?? null,
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function users(Request $request)
    {
        $settings = app('companySettings');
        $companyId = (int) ($settings?->company_id ?? 0);
        $rrCompanyId = (int) env('RR_COMPANY_ID', 1);

        $perPage = (int) (env('PER_PAGE_DATA') ?: 15);
        $search = trim((string) $request->query('search', ''));

        $query = User::query()
            ->join('companies', 'users.company_id', '=', 'companies.id')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.phone',
                'users.role',
                'users.status',
                'users.created_at',
                'users.updated_at',
            ])
            ->addSelect(DB::raw('companies.company_name as company_name'));

        if ($companyId > 0 && $companyId !== $rrCompanyId) {
            $query->where('users.parent_comp_id', $companyId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (ctype_digit($search)) {
                    $q->where('users.id', (int) $search);
                }
                $q->orWhere('users.name', 'like', '%'.$search.'%')
                    ->orWhere('users.email', 'like', '%'.$search.'%')
                    ->orWhere('companies.company_name', 'like', '%'.$search.'%');
            });
        }

        $users = $query->orderByDesc('users.id')->paginate($perPage);

        return response()->json($users);
    }

    public function companies(Request $request)
    {
        if (! $this->actsAsRrCompany()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $settings = app('companySettings');
        $companyId = $settings?->company_id;

        $companies = Companies::query()
            ->select('id', 'company_name', 'company_email', 'company_domain', 'parent_company', 'created_at')
            ->when($companyId && $companyId != env('RR_COMPANY_ID'), fn ($q) => $q->where('parent_company', $companyId))
            ->orderByDesc('id')
            ->paginate(20);

        $companies->getCollection()->transform(function ($c) {
            $active = Compemployees::query()
                ->where('parent_comp_id', $c->id)
                ->where('soft_del', 0)
                ->where('receive_label_status', '!=', 'DELIVERED')
                ->count();
            $c->active_orders = $active;
            $c->status_label = 'Active';

            return $c;
        });

        return response()->json($companies);
    }

    /** Same visibility as `companies()` list (tenant parent_company / RR). */
    private function canManageCompany(Companies $company): bool
    {
        if ($this->isAdminUser()) {
            return true;
        }
        $settings = app('companySettings');
        $tenantCompanyId = $settings?->company_id;
        if (!$tenantCompanyId) {
            return false;
        }
        $rr = (int) env('RR_COMPANY_ID');
        if ((int) $tenantCompanyId === $rr) {
            return true;
        }

        return (int) $company->parent_company === (int) $tenantCompanyId;
    }

    public function companyShow(Request $request, int $id)
    {
        if (! $this->actsAsRrCompany()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $company = Companies::query()->find($id);
        if (!$company) {
            return response()->json(['message' => 'Company not found.'], 404);
        }
        if (!$this->canManageCompany($company)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $linkedUser = User::query()->where('id', $company->user_id)->first();
        if (!$linkedUser) {
            $linkedUser = User::query()->where('company_id', $company->id)->orderBy('id')->first();
        }
        $status = strtolower((string) ($linkedUser->status ?? 'active'));
        if (! in_array($status, ['active', 'inactive'], true)) {
            $status = 'active';
        }

        $mainDomain = (string) env('MAIN_DOMAIN', '');
        $portalUrl = ($company->company_domain && $mainDomain !== '')
            ? 'http://'.$company->company_domain.'.'.$mainDomain
            : null;

        return response()->json([
            'id' => $company->id,
            'company_name' => $company->company_name,
            'domain' => $company->domain,
            'company_domain' => $company->company_domain,
            'receipient_name' => $company->receipient_name,
            'company_email' => $company->company_email,
            'company_add_1' => $company->company_add_1,
            'company_add_2' => $company->company_add_2,
            'company_city' => $company->company_city,
            'company_state' => $company->company_state,
            'company_zip' => $company->company_zip,
            'company_phone' => $company->company_phone,
            'user_status' => $status,
            'created_at' => $company->created_at?->toIso8601String(),
            'main_domain' => $mainDomain !== '' ? $mainDomain : null,
            'portal_url' => $portalUrl,
            'user' => $linkedUser ? [
                'name' => $linkedUser->name,
                'email' => $linkedUser->email,
                'phone' => $linkedUser->phone,
            ] : null,
        ]);
    }

    /**
     * Same behavior as legacy GET /update-company-status/{cid} (including activation email).
     */
    public function companyUserStatus(Request $request, int $id, MailService $mailService)
    {
        if (! $this->actsAsRrCompany()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $company = Companies::query()->find($id);
        if (! $company) {
            return response()->json(['message' => 'Company not found.'], 404);
        }
        if (! $this->canManageCompany($company)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);
        $status = $validated['status'];

        try {
            User::query()->where('company_id', $company->id)->update(['status' => $status]);

            if ($status === 'active') {
                $row = User::query()
                    ->join('companies', 'users.company_id', '=', 'companies.id')
                    ->select('users.*', 'companies.company_name as company_name', 'companies.company_domain as company_domain')
                    ->where('users.company_id', $company->id)
                    ->first();
                if ($row && $row->email) {
                    $emailData = [
                        'emailTemplate' => 'activateAccount',
                        'subject' => 'White Label Registration is Active now!',
                        'to' => $row->email,
                        'bcc' => [env('MAIL_BCC_USERNAME'), env('MAIL_BCC_USERNAME2'), env('MAIL_BCC_USERNAME3')],
                        'cc' => '',
                        'fromEmail' => env('MAIL_USERNAME'),
                        'fromName' => 'Return Device',
                        'title' => 'White Label Registration is Active now!',
                        'template' => 'activateAccount',
                        'mailData' => $row,
                        'mailTemplate' => 'mails.send_to_user',
                    ];
                    $mailService->sendMail($emailData);
                }
            }

            return response()->json(['status' => 'success', 'message' => 'Status has been updated!']);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Status cannot update.'], 500);
        }
    }

    /** Same as legacy GET /update-company-domain/{cid} (company_domain slug only). */
    public function companyPatchDomain(Request $request, int $id)
    {
        if (! $this->actsAsRrCompany()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $company = Companies::query()->find($id);
        if (! $company) {
            return response()->json(['message' => 'Company not found.'], 404);
        }
        if (! $this->canManageCompany($company)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'company_domain' => ['required', 'string', 'max:75'],
        ]);
        $slug = strtolower(preg_replace('/\s+/', '', $validated['company_domain']));

        try {
            $company->update(['company_domain' => $slug]);

            return response()->json(['status' => 'success', 'message' => 'Domain has been updated!', 'company_domain' => $slug]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Domain cannot update.'], 500);
        }
    }

    public function companyUpdate(Request $request, int $id)
    {
        if (! $this->actsAsRrCompany()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $company = Companies::query()->find($id);
        if (!$company) {
            return response()->json(['message' => 'Company not found.'], 404);
        }
        if (!$this->canManageCompany($company)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:75'],
            'domain' => ['required', 'string', 'max:75'],
            'company_domain' => ['required', 'string', 'max:75'],
            'receipient_name' => ['required', 'string', 'max:75'],
            'company_email' => ['required', 'string', 'max:75'],
            'company_add_1' => ['required', 'string', 'max:75'],
            'company_phone' => ['required', 'string', 'max:75'],
            'company_city' => ['required', 'string', 'max:45'],
            'company_state' => ['required', 'string', 'max:10'],
            'company_zip' => ['required', 'string', 'max:75'],
            'company_add_2' => ['nullable', 'string', 'max:75'],
            'user_status' => ['required', 'in:active,inactive'],
        ]);

        try {
            $company->update([
                'company_name' => $validated['company_name'],
                'domain' => $validated['domain'],
                'company_domain' => $validated['company_domain'],
                'receipient_name' => $validated['receipient_name'],
                'company_email' => $validated['company_email'],
                'company_add_1' => $validated['company_add_1'],
                'company_add_2' => $validated['company_add_2'] ?? '',
                'company_phone' => $validated['company_phone'],
                'company_city' => $validated['company_city'],
                'company_state' => $validated['company_state'],
                'company_zip' => $validated['company_zip'],
            ]);

            User::query()->where('company_id', $company->id)->update([
                'status' => $validated['user_status'],
            ]);

            return response()->json([
                'message' => 'Company updated successfully.',
                'company' => $company->fresh()->only([
                    'id', 'company_name', 'domain', 'company_domain', 'receipient_name',
                    'company_email', 'company_add_1', 'company_add_2', 'company_city',
                    'company_state', 'company_zip', 'company_phone',
                ]),
                'user_status' => $validated['user_status'],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Could not update company.'], 500);
        }
    }

    public function coupons(Request $request)
    {
        if (! $this->actsAsRrCompany()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $coupons = Coupon::query()
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($coupons);
    }

    public function couponShow(Request $request, int $id)
    {
        if (! $this->actsAsRrCompany()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $coupon = Coupon::query()->find($id);
        if (! $coupon) {
            return response()->json(['message' => 'Coupon not found.'], 404);
        }

        return response()->json([
            'id' => $coupon->id,
            'coupon' => $coupon->coupon,
            'type' => $coupon->type,
            'coupon_apply_for' => $coupon->coupon_apply_for,
            'amt_or_perc' => $coupon->amt_or_perc,
            'status' => (int) $coupon->status,
            'freeall' => (int) $coupon->freeall,
        ]);
    }

    public function couponStore(Request $request)
    {
        if (! $this->actsAsRrCompany()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'coupon_name' => ['required', 'string', 'max:10'],
            'coupon_type' => ['required', 'string', 'max:50'],
            'coupon_apply_for' => ['required', 'string', 'max:50'],
            'amt_perc' => ['required', 'numeric'],
            'freeall' => ['sometimes', 'boolean'],
        ]);

        $name = Str::upper(trim($validated['coupon_name']));
        if (Coupon::query()->where('coupon', $name)->exists()) {
            return response()->json(['message' => $name.' already exists.'], 422);
        }

        $freeall = ! empty($validated['freeall']) ? 1 : 0;

        try {
            Coupon::create([
                'coupon' => $name,
                'type' => $validated['coupon_type'],
                'coupon_apply_for' => $validated['coupon_apply_for'],
                'amt_or_perc' => $validated['amt_perc'],
                'status' => 1,
                'freeall' => $freeall,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Could not create coupon.'], 500);
        }

        return response()->json(['message' => 'Coupon created.'], 201);
    }

    public function couponUpdate(Request $request, int $id)
    {
        if (! $this->actsAsRrCompany()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $coupon = Coupon::query()->find($id);
        if (! $coupon) {
            return response()->json(['message' => 'Coupon not found.'], 404);
        }

        $validated = $request->validate([
            'coupon_name' => ['required', 'string', 'max:10'],
            'coupon_type' => ['required', 'string', 'max:50'],
            'coupon_apply_for' => ['required', 'string', 'max:50'],
            'amt_perc' => ['required', 'numeric'],
            'status' => ['required', 'integer', 'in:0,1'],
            'freeall' => ['sometimes', 'boolean'],
        ]);

        $name = Str::upper(trim($validated['coupon_name']));
        if (Coupon::query()->where('coupon', $name)->where('id', '!=', $id)->exists()) {
            return response()->json(['message' => $name.' already exists.'], 422);
        }

        $freeall = ! empty($validated['freeall']) ? 1 : 0;

        try {
            $coupon->update([
                'coupon' => $name,
                'type' => $validated['coupon_type'],
                'coupon_apply_for' => $validated['coupon_apply_for'],
                'amt_or_perc' => $validated['amt_perc'],
                'status' => (int) $validated['status'],
                'freeall' => $freeall,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Could not update coupon.'], 500);
        }

        return response()->json(['message' => 'Coupon updated.']);
    }
}

