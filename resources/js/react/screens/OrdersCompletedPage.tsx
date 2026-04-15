import { useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { Download, Eye } from 'lucide-react';
import { saasAxios } from '../api/saasAxios';
import { useToast } from '../context/ToastContext';

type OrderRow = {
  item_id: number;
  equip_type?: string | null;
  order_id?: number | null;
  customer_name?: string | null;
  order_createdAt?: string | null;
  completed_at?: string | null;
  final_amount?: number | string | null;
  receive_label_status?: string | null;
};

type Paginated<T> = {
  data: T[];
  current_page?: number;
  last_page?: number;
  total?: number;
  from?: number | null;
  to?: number | null;
  per_page?: number;
};

function formatOrderId(orderId?: number | null, itemId?: number) {
  if (orderId) return `#ORD-${orderId}`;
  if (itemId) return `#${itemId}`;
  return '—';
}

export function OrdersCompletedPage() {
  const { showToast } = useToast();
  const [page, setPage] = useState(1);
  const [resp, setResp] = useState<Paginated<OrderRow> | null>(null);
  const [loading, setLoading] = useState(true);
  const [exporting, setExporting] = useState(false);

  useEffect(() => {
    setLoading(true);
    saasAxios
      .get('/api/saas/orders?status=completed', { params: { page } })
      .then((r) => setResp(r.data))
      .catch(() => setResp({ data: [] }))
      .finally(() => setLoading(false));
  }, [page]);

  const rows = resp?.data ?? [];
  const total = resp?.total ?? rows.length;
  const from = resp?.from ?? (rows.length ? (page - 1) * (resp?.per_page ?? 15) + 1 : 0);
  const to = resp?.to ?? from + rows.length - 1;

  const summary = useMemo(() => {
    if (loading) return 'Loading…';
    if (!rows.length) return 'Showing 0 orders';
    return `Showing ${from} to ${to} of ${total} orders`;
  }, [from, to, total, rows.length, loading]);

  async function onExport() {
    setExporting(true);
    try {
      const res = await saasAxios.get('/api/saas/orders/export', {
        params: { status: 'completed', label_status: 'all' },
        responseType: 'blob',
      });
      const blob = new Blob([res.data], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `completed-orders-${new Date().toISOString().slice(0, 10)}.csv`;
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);
      showToast('Export downloaded.', 'success');
    } catch {
      showToast('Could not export. Try again.', 'error');
    } finally {
      setExporting(false);
    }
  }

  return (
    <div className="content-wrapper">
      <div className="page-header reveal active">
        <h1 className="page-title">Completed Orders</h1>
        <div className="toolbar">
          <button className="btn btn-outline" type="button" onClick={onExport} disabled={exporting || loading}>
            <Download size={18} /> {exporting ? 'Exporting…' : 'Export History'}
          </button>
        </div>
      </div>

      <div className="card reveal active">
        <div className="table-responsive">
          <table className="data-table">
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Equipment</th>
                <th>Status</th>
                <th>Completed Date</th>
                <th>Final Amount</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {rows.map((r) => (
                <tr key={r.item_id}>
                  <td>{formatOrderId(r.order_id, r.item_id)}</td>
                  <td>{r.customer_name ?? '—'}</td>
                  <td>{r.equip_type ?? '—'}</td>
                  <td>
                    <span className="status-pill status-completed">
                      {r.receive_label_status === 'DELIVERED'
                        ? 'Delivered'
                        : r.receive_label_status ?? 'Delivered'}
                    </span>
                  </td>
                  <td>
                    {(r.completed_at ?? r.order_createdAt)
                      ? String(r.completed_at ?? r.order_createdAt).slice(0, 10)
                      : '—'}
                  </td>
                  <td>
                    {r.final_amount != null && r.final_amount !== ''
                      ? `$${Number(r.final_amount).toFixed(2)}`
                      : '—'}
                  </td>
                  <td>
                    <Link to={`/orders/${r.item_id}`} className="icon-btn" aria-label="View order">
                      <Eye size={18} />
                    </Link>
                  </td>
                </tr>
              ))}
              {!loading && rows.length === 0 ? (
                <tr>
                  <td colSpan={7}>No results.</td>
                </tr>
              ) : null}
            </tbody>
          </table>
        </div>
        <div
          className="pagination"
          style={{
            marginTop: 20,
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center',
          }}
        >
          <span style={{ fontSize: 14, color: 'var(--text-muted)' }}>{summary}</span>
          <div style={{ display: 'flex', gap: 8 }}>
            <button
              type="button"
              className="btn btn-outline btn-sm"
              disabled={page <= 1 || loading}
              onClick={() => setPage((p) => Math.max(1, p - 1))}
            >
              Previous
            </button>
            <button
              type="button"
              className="btn btn-outline btn-sm"
              disabled={!resp || (resp.last_page != null && page >= (resp.last_page ?? 1)) || loading}
              onClick={() => setPage((p) => p + 1)}
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
