import { useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { Check, ChevronDown, Download, Filter, MoreHorizontal } from 'lucide-react';
import { saasAxios } from '../api/saasAxios';
import { useToast } from '../context/ToastContext';
import { useSaasMe } from '../context/SaasMeContext';

type OrderRow = {
  item_id: number;
  equip_type?: string | null;
  order_id?: number | null;
  customer_name?: string | null;
  payStatus?: string | null;
  order_status?: string | null;
  order_createdAt?: string | null;
  send_label_status?: string | null;
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

function shipmentStatusPill(row: OrderRow) {
  const pay = (row.payStatus ?? row.order_status ?? '').toLowerCase();
  const recv = (row.receive_label_status ?? '').toUpperCase();
  if (recv === 'DELIVERED') {
    return { text: 'Delivered', cls: 'status-pill status-completed' };
  }
  if (pay.includes('pending') || pay.includes('label')) {
    return { text: 'Label Created', cls: 'status-pill status-pending' };
  }
  return { text: 'In Transit', cls: 'status-pill status-progress' };
}

function labelStatePill(row: OrderRow) {
  const recv = (row.receive_label_status ?? '').toUpperCase();
  const send = String(row.send_label_status ?? '').trim();
  if (recv && recv !== 'DELIVERED' && send) {
    return {
      text: 'Pending Pickup',
      style: { background: '#fef3c7', color: '#92400e' } as React.CSSProperties,
    };
  }
  return {
    text: 'Active',
    style: { background: '#e0f2fe', color: '#0369a1' } as React.CSSProperties,
  };
}

export function OrdersInProgressPage() {
  const { showToast } = useToast();
  const me = useSaasMe();
  const isAdmin = me?.is_rr_company === true;
  const [page, setPage] = useState(1);
  const [resp, setResp] = useState<Paginated<OrderRow> | null>(null);
  const [filterOpen, setFilterOpen] = useState(false);
  const [statusOpen, setStatusOpen] = useState(false);
  const [statusFilter, setStatusFilter] = useState<'all' | 'in_transit' | 'label_created'>('all');
  const [menuPushPx, setMenuPushPx] = useState(0);

  useEffect(() => {
    const measure = () => {
      const header = document.querySelector('.content-wrapper .page-header') as HTMLElement | null;
      const menus = Array.from(
        document.querySelectorAll('.content-wrapper .select-menu, .content-wrapper .filter-popover'),
      ) as HTMLElement[];
      const hb = header?.getBoundingClientRect().bottom ?? 0;
      const maxMenuBottom = menus.reduce((acc, el) => Math.max(acc, el.getBoundingClientRect().bottom), hb);
      const next =
        menus.length > 0 ? Math.max(0, Math.ceil(maxMenuBottom - hb + 12)) : 0;
      setMenuPushPx(next);
    };
    const raf = window.requestAnimationFrame(measure);
    return () => window.cancelAnimationFrame(raf);
  }, [filterOpen, statusOpen]);

  useEffect(() => {
    const labelStatus =
      statusFilter === 'label_created'
        ? 'label'
        : statusFilter === 'in_transit'
          ? 'in_transit'
          : 'all';
    saasAxios
      .get('/api/saas/orders?status=in_progress', { params: { page, label_status: labelStatus } })
      .then((r) => setResp(r.data))
      .catch(() => setResp({ data: [] }));
  }, [page, statusFilter]);

  const statusLabel = useMemo(() => {
    if (statusFilter === 'label_created') return 'Label Created';
    if (statusFilter === 'in_transit') return 'In Transit';
    return 'All';
  }, [statusFilter]);

  async function onExport() {
    try {
      const labelStatus =
        statusFilter === 'label_created'
          ? 'label'
          : statusFilter === 'in_transit'
            ? 'in_transit'
            : 'all';
      const res = await saasAxios.get('/api/saas/orders/export', {
        params: { status: 'in_progress', label_status: labelStatus },
        responseType: 'blob',
      });
      const blob = new Blob([res.data], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `in-progress-orders-${labelStatus}-${new Date().toISOString().slice(0, 10)}.csv`;
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);
      showToast('Export downloaded.', 'success');
    } catch {
      showToast('Could not export. Try again.', 'error');
    }
  }

  const rows = resp?.data ?? [];
  const total = resp?.total ?? rows.length;
  const from = resp?.from ?? (rows.length ? (page - 1) * (resp?.per_page ?? 15) + 1 : 0);
  const to = resp?.to ?? from + rows.length - 1;

  const summary = useMemo(() => {
    if (!rows.length) return 'Showing 0 orders';
    return `Showing ${from} to ${to} of ${total} orders`;
  }, [from, to, total, rows.length]);

  return (
    <div className="content-wrapper">
      <div className="page-header reveal active">
        <h1 className="page-title">In-Progress Orders</h1>
        <div className="toolbar">
          <div className="filter-wrap">
            <button className="btn btn-outline" type="button" onClick={() => setFilterOpen((v) => !v)}>
              <Filter size={18} /> Filter
            </button>
            {filterOpen ? (
              <div className="filter-popover card" role="dialog" aria-label="In-progress order filters">
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
                            { value: 'label_created', label: 'Label Created' },
                            { value: 'in_transit', label: 'In Transit' },
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
                                setPage(1);
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
            <Download size={18} /> Export CSV
          </button>
        </div>
      </div>
      {menuPushPx > 0 ? <div aria-hidden="true" style={{ height: menuPushPx }} /> : null}

      <div className="card reveal active">
        <div className="table-responsive">
          <table className="data-table">
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Equipment</th>
                <th>Status</th>
                <th>Label State</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {rows.map((r) => {
                const ship = shipmentStatusPill(r);
                const label = labelStatePill(r);
                return (
                  <tr key={r.item_id}>
                    <td>{formatOrderId(r.order_id, r.item_id)}</td>
                    <td>{r.customer_name ?? '—'}</td>
                    <td>{r.equip_type ?? '—'}</td>
                    <td>
                      <span className={ship.cls}>{ship.text}</span>
                    </td>
                    <td>
                      <span className="status-pill" style={label.style}>
                        {label.text}
                      </span>
                    </td>
                    <td>
                      {r.order_createdAt
                        ? String(r.order_createdAt).slice(0, 10)
                        : '—'}
                    </td>
                    <td>
                      <div style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
                        <Link to={`/orders/${r.item_id}`} className="icon-btn" aria-label="View order">
                          <MoreHorizontal size={18} />
                        </Link>
                        {isAdmin ? (
                          <Link
                            to={`/orders/${r.item_id}/edit`}
                            className="btn btn-outline btn-sm"
                            style={{ padding: '6px 10px' }}
                            aria-label="Edit order"
                          >
                            Edit
                          </Link>
                        ) : null}
                      </div>
                    </td>
                  </tr>
                );
              })}
              {resp && rows.length === 0 ? (
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
              disabled={page <= 1}
              onClick={() => setPage((p) => Math.max(1, p - 1))}
            >
              Previous
            </button>
            <button
              type="button"
              className="btn btn-outline btn-sm"
              disabled={
                !resp || (resp.last_page != null && page >= (resp.last_page ?? 1))
              }
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
