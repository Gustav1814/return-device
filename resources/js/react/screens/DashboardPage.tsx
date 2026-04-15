import { useCallback, useEffect, useMemo, useState } from 'react';
import Chart from 'chart.js/auto';
import { saasAxios } from '../api/saasAxios';
import { useToast } from '../context/ToastContext';
import { useSaasSettings } from '../hooks/useSaasSettings';
import {
  chartAxisColors,
  chartDistributionPalette,
  chartSecondaryFromPrimary,
  chartTooltipTheme,
  clampAccentForUiTheme,
  normalizeHex,
  parseHexColor,
} from '../utils/chartTheme';
import { Check, ChevronDown, Download, Filter, TrendingDown, TrendingUp } from 'lucide-react';
import { Link, useLocation } from 'react-router-dom';

type WidgetRow = { id: string; visible: boolean };

const DEFAULT_CHARTS = [
  { id: 'shipments_trend', visible: true },
  { id: 'status_breakdown', visible: true },
];

/** API / DB may store visible as bool, 0/1, or string (legacy JSON). */
function normalizeVisible(raw: unknown): boolean | undefined {
  if (typeof raw === 'boolean') return raw;
  if (raw === 1 || raw === '1') return true;
  if (raw === 0 || raw === '0') return false;
  if (raw === 'true') return true;
  if (raw === 'false') return false;
  return undefined;
}

function mergeCharts(raw: unknown): WidgetRow[] {
  if (!Array.isArray(raw)) return DEFAULT_CHARTS;
  const byId = new Map(DEFAULT_CHARTS.map((c) => [c.id, { ...c }]));
  for (const row of raw as { id?: string; visible?: unknown }[]) {
    const id = row?.id;
    if (!id || !byId.has(id)) continue;
    const v = normalizeVisible(row.visible);
    if (v !== undefined) byId.set(id, { id, visible: v });
  }
  return DEFAULT_CHARTS.map((d) => byId.get(d.id) ?? d);
}

function Trend({ pct }: { pct: number | null | undefined }) {
  // Always render to match the original static KPI cards (arrow + % line).
  const n = typeof pct === 'number' && isFinite(pct) ? pct : 0;
  const up = n >= 0;
  const Icon = up ? TrendingUp : TrendingDown;
  return (
    <div className={['kpi-trend', up ? 'trend-up' : 'trend-down'].join(' ')}>
      <Icon /> {Math.abs(n).toFixed(1)}%
    </div>
  );
}

function formatCount(v: unknown) {
  const n = typeof v === 'number' ? v : Number(v);
  if (!isFinite(n)) return '—';
  return Math.round(n).toLocaleString();
}

function chartToggleVisible(chartToggles: WidgetRow[], id: string) {
  const w = chartToggles.find((x) => x.id === id);
  return w?.visible !== false;
}

/** `YYYY-MM` from API → short label for chart axis */
function ymToShortLabel(ym: string) {
  const [y, m] = ym.split('-').map(Number);
  if (!y || !m) return ym;
  return new Date(y, m - 1, 1).toLocaleDateString(undefined, { month: 'short', year: '2-digit' });
}

function formatAxisMoney(value: number) {
  const n = Number(value);
  if (!isFinite(n)) return '$0';
  if (Math.abs(n) >= 1000) return `$${(n / 1000).toFixed(n % 1000 === 0 ? 0 : 1)}k`;
  return `$${Math.round(n)}`;
}

function distributionRows(charts: any): { label: string; cnt: number }[] {
  const equip = charts?.equipmentDistribution;
  if (Array.isArray(equip) && equip.length > 0) {
    return equip.map((r: any) => ({
      label: String(r.type_of_equip ?? 'Unknown'),
      cnt: Number(r.cnt ?? 0),
    }));
  }
  const st = charts?.statusDistribution;
  if (Array.isArray(st) && st.length > 0) {
    return st.map((r: any) => ({
      label: String(r.receive_label_status ?? 'Unknown'),
      cnt: Number(r.cnt ?? 0),
    }));
  }
  return [];
}

export function DashboardPage() {
  const { settings: saasSettings, theme: uiTheme } = useSaasSettings();
  const { showToast } = useToast();
  const location = useLocation();
  const [data, setData] = useState<any>(null);
  const [charts, setCharts] = useState<any>(null);
  const [settingsRow, setSettingsRow] = useState<any>(null);
  const [chartUiTick, setChartUiTick] = useState(0);
  const [range, setRange] = useState<'30d' | '90d' | 'ytd'>('30d');
  const [statusFilter, setStatusFilter] = useState<'all' | 'in_progress' | 'completed'>('all');
  const [filterOpen, setFilterOpen] = useState(false);
  const [rangeOpen, setRangeOpen] = useState(false);
  const [statusOpen, setStatusOpen] = useState(false);
  const [menuPushPx, setMenuPushPx] = useState(0);

  const primaryHex = useMemo(() => {
    const sd = saasSettings?.settings_data ?? {};
    const raw =
      (typeof sd.primaryColorUser === 'string' &&
        sd.primaryColorUser.trim() &&
        sd.primaryColorUser.startsWith('#') &&
        sd.primaryColorUser) ||
      (typeof saasSettings?.btn_bg_color === 'string' &&
        saasSettings.btn_bg_color.trim() &&
        saasSettings.btn_bg_color.startsWith('#') &&
        saasSettings.btn_bg_color) ||
      (typeof sd.primaryColor === 'string' && sd.primaryColor.trim() && sd.primaryColor.startsWith('#') && sd.primaryColor) ||
      '#6366f1';
    const norm = normalizeHex(raw) ?? '#6366f1';
    return clampAccentForUiTheme(norm, uiTheme);
  }, [saasSettings, uiTheme]);

  useEffect(() => {
    const el = document.documentElement;
    const obs = new MutationObserver(() => setChartUiTick((n) => n + 1));
    obs.observe(el, { attributes: true, attributeFilter: ['class', 'data-theme'] });
    return () => obs.disconnect();
  }, []);

  useEffect(() => {
    const measure = () => {
      const header = document.querySelector('.page-header') as HTMLElement | null;
      const menus = Array.from(document.querySelectorAll('.select-menu, .filter-popover')) as HTMLElement[];
      const hb = header?.getBoundingClientRect().bottom ?? 0;
      const maxMenuBottom = menus.reduce((acc, el) => Math.max(acc, el.getBoundingClientRect().bottom), hb);
      const next =
        menus.length > 0
          ? Math.max(0, Math.ceil(maxMenuBottom - hb + 12)) // +gap so cards never touch menu
          : 0;
      setMenuPushPx(next);
    };
    const raf = window.requestAnimationFrame(measure);
    return () => window.cancelAnimationFrame(raf);
  }, [rangeOpen, filterOpen, statusOpen]);

  const statusLabel = useMemo(() => {
    if (statusFilter === 'completed') return 'Completed';
    if (statusFilter === 'in_progress') return 'In progress';
    return 'All';
  }, [statusFilter]);

  const rangeLabel = useMemo(() => {
    if (range === '90d') return 'Last 90 Days';
    if (range === 'ytd') return 'This Year';
    return 'Last 30 Days';
  }, [range]);

  const trendsCanvasId = useMemo(() => 'shipmentsTrendChart', []);
  const equipCanvasId = useMemo(() => 'equipmentDistChart', []);

  const chartToggles = useMemo(
    () => mergeCharts(settingsRow?.settings_data?.dashboard?.charts),
    [settingsRow],
  );

  const showTrendChart = chartToggleVisible(chartToggles, 'shipments_trend');
  const showEquipChart = chartToggleVisible(chartToggles, 'status_breakdown');
  const trendHasSeries =
    Array.isArray(charts?.trends?.labels) && (charts.trends.labels as unknown[]).length > 0;

  const refreshSettings = useCallback(() => {
    saasAxios
      .get('/api/saas/settings')
      .then((r) => setSettingsRow(r.data))
      .catch(() => setSettingsRow(null));
  }, []);

  useEffect(() => {
    refreshSettings();
  }, [refreshSettings, location.pathname, location.key]);

  const fetchDashboardData = useCallback(() => {
    const params = { range, status: statusFilter };
    saasAxios
      .get('/api/saas/dashboard', { params })
      .then((r) => setData(r.data))
      .catch(() => setData(null));
    saasAxios
      .get('/api/saas/dashboard/charts', { params })
      .then((r) => setCharts(r.data))
      .catch(() => setCharts(null));
  }, [range, statusFilter]);

  useEffect(() => {
    fetchDashboardData();
  }, [fetchDashboardData]);

  useEffect(() => {
    const id = window.setInterval(fetchDashboardData, 60_000);
    return () => window.clearInterval(id);
  }, [fetchDashboardData]);

  useEffect(() => {
    const onVis = () => {
      if (document.visibilityState === 'visible') {
        fetchDashboardData();
      }
    };
    document.addEventListener('visibilitychange', onVis);
    return () => document.removeEventListener('visibilitychange', onVis);
  }, [fetchDashboardData]);

  async function onExport() {
    try {
      const res = await saasAxios.get('/api/saas/dashboard/export', {
        params: { range, status: statusFilter },
        responseType: 'blob',
      });

      const blob = new Blob([res.data], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `dashboard-export-${range}-${statusFilter}-${new Date().toISOString().slice(0, 10)}.csv`;
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);
      showToast('Export downloaded.', 'success');
    } catch {
      showToast('Could not export. Try again.', 'error');
    }
  }

  useEffect(() => {
    if (!showTrendChart || !charts?.trends?.labels?.length) return;
    const el = document.getElementById(trendsCanvasId) as HTMLCanvasElement | null;
    if (!el) return;

    const ctx = el.getContext('2d');
    if (!ctx) return;

    const isDark = document.documentElement.classList.contains('dark');
    const secondaryHex = chartSecondaryFromPrimary(primaryHex, isDark);
    const axis = chartAxisColors(isDark);
    const tt = chartTooltipTheme(isDark);
    const pr = parseHexColor(primaryHex);
    const sr = parseHexColor(secondaryHex);
    const revenueTransparent = pr ? `rgba(${pr.r},${pr.g},${pr.b},0)` : 'rgba(99,102,241,0)';
    const marginTransparent = sr ? `rgba(${sr.r},${sr.g},${sr.b},0)` : 'rgba(16,185,129,0)';

    const gradRevenue = ctx.createLinearGradient(0, 0, 0, 350);
    gradRevenue.addColorStop(0, primaryHex);
    gradRevenue.addColorStop(1, revenueTransparent);

    const gradMargin = ctx.createLinearGradient(0, 0, 0, 350);
    gradMargin.addColorStop(0, secondaryHex);
    gradMargin.addColorStop(1, marginTransparent);

    const rawLabels = charts.trends.labels as string[];
    const displayLabels = rawLabels.map(ymToShortLabel);
    const revenue = (charts.trends.revenue ?? []).map((v: any) => Number(v ?? 0));
    const marginFromApi = Array.isArray(charts.trends.margin)
      ? (charts.trends.margin as any[]).map((v) => Number(v ?? 0))
      : [];
    const margin =
      marginFromApi.length === revenue.length
        ? marginFromApi
        : rawLabels.map(() => 0);

    const c = new Chart(el, {
      type: 'bar',
      data: {
        labels: displayLabels,
        datasets: [
          {
            label: 'Revenue',
            data: revenue,
            backgroundColor: gradRevenue,
            borderColor: primaryHex,
            borderWidth: 2,
            borderRadius: 8,
            barPercentage: 0.6,
          },
          {
            label: 'Margin',
            data: margin,
            backgroundColor: gradMargin,
            borderColor: secondaryHex,
            borderWidth: 2,
            borderRadius: 8,
            barPercentage: 0.6,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: tt.bg,
            titleColor: tt.titleColor,
            bodyColor: tt.bodyColor,
            padding: 16,
            titleFont: { size: 14, weight: 'bold' },
            bodyFont: { size: 13 },
            cornerRadius: 12,
            displayColors: true,
            callbacks: {
              label: (context) =>
                ` ${context.dataset.label}: $${Number(context.parsed.y ?? 0).toLocaleString()}`,
            },
          },
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { color: axis.tick, font: { size: 12, weight: '500' } },
          },
          y: {
            grid: { color: axis.grid, drawBorder: false },
            ticks: {
              color: axis.tick,
              font: { size: 11 },
              callback: (value) => formatAxisMoney(Number(value)),
            },
          },
        },
      },
    });

    return () => c.destroy();
  }, [charts, trendsCanvasId, showTrendChart, primaryHex, chartUiTick]);

  useEffect(() => {
    if (!showEquipChart || !charts) return;
    const rows = distributionRows(charts);
    if (!rows.length) return;
    const el = document.getElementById(equipCanvasId) as HTMLCanvasElement | null;
    if (!el) return;

    const labels = rows.map((r) => r.label);
    const values = rows.map((r) => r.cnt);

    const isDark = document.documentElement.classList.contains('dark');
    const colors = chartDistributionPalette(primaryHex, values.length, isDark);

    const c = new Chart(el, {
      type: 'doughnut',
      data: {
        labels,
        datasets: [
          {
            data: values,
            backgroundColor: values.map((_: unknown, i: number) => colors[i % colors.length]),
            borderWidth: 0,
            hoverOffset: 10,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        cutout: '70%',
      },
    });

    return () => c.destroy();
  }, [charts, equipCanvasId, showEquipChart, primaryHex, chartUiTick]);

  const doughnutRows = charts ? distributionRows(charts) : [];
  const doughnutTitle =
    Array.isArray(charts?.equipmentDistribution) && charts.equipmentDistribution.length > 0
      ? 'Equipment Distribution'
      : 'Shipment status';

  const salesLegendMargin = useMemo(() => {
    const isDark = document.documentElement.classList.contains('dark');
    return chartSecondaryFromPrimary(primaryHex, isDark);
  }, [primaryHex, chartUiTick]);

  const doughnutLegendColors = useMemo(() => {
    const isDark = document.documentElement.classList.contains('dark');
    const n = Math.max(doughnutRows.length, 1);
    return chartDistributionPalette(primaryHex, n, isDark);
  }, [primaryHex, doughnutRows.length, chartUiTick]);

  return (
    <div className="content-wrapper">
      <div className="page-header reveal active">
        <h1 className="page-title">Dashboard</h1>
        <div className="toolbar">
          <div className="select-wrap">
            <button
              className="btn btn-outline select-btn"
              type="button"
              aria-haspopup="listbox"
              aria-expanded={rangeOpen}
              onClick={() => setRangeOpen((v) => !v)}
            >
              {rangeLabel} <ChevronDown />
            </button>
            {rangeOpen ? (
              <div className="select-menu card" role="listbox" aria-label="Date range">
                {(
                  [
                    { value: '30d', label: 'Last 30 Days' },
                    { value: '90d', label: 'Last 90 Days' },
                    { value: 'ytd', label: 'This Year' },
                  ] as const
                ).map((opt) => {
                  const active = opt.value === range;
                  return (
                    <button
                      key={opt.value}
                      type="button"
                      className={['select-item', active ? 'active' : ''].join(' ')}
                      role="option"
                      aria-selected={active}
                      onClick={() => {
                        setRange(opt.value);
                        setRangeOpen(false);
                      }}
                    >
                      <span className="label">{opt.label}</span>
                      {active ? <Check /> : <span className="spacer" aria-hidden="true" />}
                    </button>
                  );
                })}
              </div>
            ) : null}
          </div>
          <div className="filter-wrap">
            <button
              className="btn btn-outline"
              type="button"
              onClick={() => setFilterOpen((v) => !v)}
            >
            <Filter /> Filter
            </button>
            {filterOpen ? (
              <div className="filter-popover card" role="dialog" aria-label="Dashboard filters">
                <div className="filter-row">
                  <label className="filter-label">Shipment status</label>
                  <div className="select-wrap">
                    <button
                      className="filter-select filter-select-btn"
                      type="button"
                      aria-haspopup="listbox"
                      aria-expanded={statusOpen}
                      onClick={() => setStatusOpen((v) => !v)}
                    >
                      {statusLabel} <ChevronDown />
                    </button>
                    {statusOpen ? (
                      <div className="select-menu card" role="listbox" aria-label="Shipment status">
                        {(
                          [
                            { value: 'all', label: 'All' },
                            { value: 'in_progress', label: 'In progress' },
                            { value: 'completed', label: 'Completed' },
                          ] as const
                        ).map((opt) => {
                          const active = opt.value === statusFilter;
                          return (
                            <button
                              key={opt.value}
                              type="button"
                              className={['select-item', active ? 'active' : ''].join(' ')}
                              role="option"
                              aria-selected={active}
                              onClick={() => {
                                setStatusFilter(opt.value);
                                setStatusOpen(false);
                              }}
                            >
                              <span className="label">{opt.label}</span>
                              {active ? <Check /> : <span className="spacer" aria-hidden="true" />}
                            </button>
                          );
                        })}
                      </div>
                    ) : null}
                  </div>
                </div>
                <div className="filter-actions">
                  <button className="btn btn-outline btn-sm" type="button" onClick={() => setFilterOpen(false)}>
                    Close
                  </button>
                </div>
              </div>
            ) : null}
          </div>
          <button className="btn btn-outline" type="button" onClick={onExport}>
            <Download /> Export
          </button>
        </div>
      </div>
      {menuPushPx > 0 ? <div aria-hidden="true" style={{ height: menuPushPx }} /> : null}

      <div className="kpi-grid reveal active">
        <div className="card kpi-card" id="kpi_in_progress">
          <span className="kpi-label">In Progress Shipments</span>
          <div className="kpi-value">{formatCount(data?.kpis?.inProgress)}</div>
          <Trend pct={data?.kpis?.trends?.inProgressPct} />
        </div>
        <div className="card kpi-card" id="kpi_completed">
          <span className="kpi-label">Completed Shipments</span>
          <div className="card-icon" />
          <div className="kpi-value">{formatCount(data?.kpis?.completed)}</div>
          <Trend pct={data?.kpis?.trends?.completedPct} />
        </div>
        <div className="card kpi-card" id="kpi_total_amount">
          <span className="kpi-label">Total Paid Amount</span>
          <div className="kpi-value">
            {typeof data?.kpis?.totalPaid === 'number' ? `$${data.kpis.totalPaid.toFixed(2)}` : '—'}
          </div>
          <Trend pct={data?.kpis?.trends?.totalPaidPct} />
        </div>
      </div>

      <div className="dashboard-row">
        {showTrendChart ? (
          <div className="card reveal active">
            <div className="card-header">
              <h3 className="card-title">Sales Overview</h3>
              <div className="card-actions">
                <span className="legend-item">
                  <span className="dot" style={{ background: primaryHex }} /> Revenue
                </span>
                <span className="legend-item">
                  <span className="dot" style={{ background: salesLegendMargin }} /> Margin
                </span>
              </div>
            </div>
            {trendHasSeries ? (
              <div className="chart-container">
                <canvas id={trendsCanvasId}></canvas>
              </div>
            ) : (
              <p className="chart-empty">
                No sales trend data for this date range and filter. Try a wider range or different shipment
                status.
              </p>
            )}
          </div>
        ) : null}

        {showEquipChart ? (
          <div className="card reveal active">
            <div className="card-header">
              <h3 className="card-title">{doughnutTitle}</h3>
            </div>
            {doughnutRows.length > 0 ? (
              <>
                <div className="chart-container">
                  <canvas id={equipCanvasId}></canvas>
                </div>
                <div className="chart-legend" id="equipment-legend">
                  <div className="legend-grid">
                    {(() => {
                      const total =
                        doughnutRows.reduce((acc, r) => acc + r.cnt, 0) || 1;
                      return doughnutRows.map((r, i) => {
                        const pct = Math.round((r.cnt / total) * 100);
                        return (
                          <div className="legend-item-detailed" key={`${r.label}-${i}`}>
                            <span
                              className="dot"
                              style={{ background: doughnutLegendColors[i % doughnutLegendColors.length] }}
                            />
                            <span className="label">{r.label}</span>
                            <span className="value">{pct}%</span>
                          </div>
                        );
                      });
                    })()}
                  </div>
                </div>
              </>
            ) : (
              <p className="chart-empty">
                No equipment or status breakdown for this date range and filter. The chart will appear when
                there is data.
              </p>
            )}
          </div>
        ) : null}
      </div>

      <div className="dashboard-row reveal active" style={{ marginTop: 24 }}>
        <div className="card table-card">
          <div className="card-header">
            <h3 className="card-title">Recent Shipments</h3>
            <Link to="/orders/in-progress" className="btn-link">
              See all
            </Link>
          </div>
          <div className="table-responsive">
            <table className="data-table">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Equipment</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Amount</th>
                </tr>
              </thead>
              <tbody>
                {(data?.recentShipments ?? []).map((r: any) => (
                  <tr key={r.item_id}>
                    <td>{r.order_id ? `#ORD-${r.order_id}` : `#${r.item_id}`}</td>
                    <td>{r.equip_type ?? '-'}</td>
                    <td>
                      <span
                        className={[
                          'status-pill',
                          String(r.label_status ?? '').toUpperCase() === 'DELIVERED'
                            ? 'status-completed'
                            : 'status-progress',
                        ].join(' ')}
                      >
                        {r.label_status ?? 'In Progress'}
                      </span>
                    </td>
                    <td>{r.created_at ? String(r.created_at).slice(0, 10) : '-'}</td>
                    <td>
                      {r.amount != null ? `$${Number(r.amount).toFixed(2)}` : '-'}
                    </td>
                  </tr>
                ))}
                {Array.isArray(data?.recentShipments) && data.recentShipments.length === 0 ? (
                  <tr>
                    <td colSpan={5}>No recent shipments.</td>
                  </tr>
                ) : null}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  );
}
