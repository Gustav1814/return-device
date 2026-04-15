import { useCallback, useEffect, useState } from 'react';
import { MoreHorizontal, X } from 'lucide-react';
import { saasAxios } from '../api/saasAxios';
import { useToast } from '../context/ToastContext';

function discountLabel(c: any) {
  const t = String(c.type ?? '').toLowerCase();
  const v = c.amt_or_perc;
  if (t.includes('percent') || t === '%' || t === 'perc') return `${v}% OFF`;
  if (v != null && v !== '') return `$${Number(v).toFixed(2)}`;
  return String(v ?? '—');
}

function couponStatusPill(c: any) {
  const s = String(c.status ?? '').toLowerCase();
  if (s === '1' || s === 'active') return { cls: 'status-pill status-completed', text: 'Active' };
  if (s === 'scheduled') return { cls: 'status-pill status-pending', text: 'Scheduled' };
  return { cls: 'status-pill status-pending', text: c.status ?? '—' };
}

type CouponForm = {
  coupon_name: string;
  coupon_type: string;
  coupon_apply_for: string;
  amt_perc: string;
  status: 0 | 1;
  freeall: boolean;
};

function emptyForm(): CouponForm {
  return {
    coupon_name: '',
    coupon_type: 'amount',
    coupon_apply_for: 'total',
    amt_perc: '',
    status: 1,
    freeall: false,
  };
}

export function CouponsPage() {
  const { showToast } = useToast();
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [menuId, setMenuId] = useState<number | null>(null);

  const [modalOpen, setModalOpen] = useState(false);
  const [editId, setEditId] = useState<number | null>(null);
  const [formLoading, setFormLoading] = useState(false);
  const [formSaving, setFormSaving] = useState(false);
  const [form, setForm] = useState<CouponForm>(() => emptyForm());
  const [formError, setFormError] = useState<string | null>(null);

  const reload = useCallback(() => {
    setLoading(true);
    saasAxios
      .get('/api/saas/coupons')
      .then((r) => setData(r.data))
      .catch(() => setData({ data: [] }))
      .finally(() => setLoading(false));
  }, []);

  useEffect(() => {
    reload();
  }, [reload]);

  useEffect(() => {
    if (menuId == null) return;
    const close = () => setMenuId(null);
    document.addEventListener('click', close);
    return () => document.removeEventListener('click', close);
  }, [menuId]);

  useEffect(() => {
    if (!modalOpen) return;
    const onKey = (e: KeyboardEvent) => {
      if (e.key === 'Escape' && !formSaving) {
        setModalOpen(false);
        setEditId(null);
        setFormError(null);
      }
    };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [modalOpen, formSaving]);

  function openCreate() {
    setMenuId(null);
    setEditId(null);
    setForm(emptyForm());
    setFormError(null);
    setModalOpen(true);
  }

  async function openEdit(id: number) {
    setMenuId(null);
    setEditId(id);
    setForm(emptyForm());
    setFormError(null);
    setModalOpen(true);
    setFormLoading(true);
    try {
      const { data: row } = await saasAxios.get<{
        coupon: string;
        type: string;
        coupon_apply_for: string;
        amt_or_perc: number | string;
        status: number;
        freeall: number;
      }>(`/api/saas/coupons/${id}`);
      setForm({
        coupon_name: String(row.coupon ?? ''),
        coupon_type: String(row.type ?? 'amount'),
        coupon_apply_for: String(row.coupon_apply_for ?? 'total'),
        amt_perc: String(row.amt_or_perc ?? ''),
        status: row.status === 0 ? 0 : 1,
        freeall: Number(row.freeall) === 1,
      });
    } catch {
      setFormError('Could not load coupon.');
      showToast('Could not load coupon.', 'default');
    } finally {
      setFormLoading(false);
    }
  }

  function closeModal() {
    if (formSaving) return;
    setModalOpen(false);
    setEditId(null);
    setFormError(null);
  }

  async function saveCoupon() {
    setFormError(null);
    const name = form.coupon_name.trim();
    if (!name) {
      setFormError('Coupon code is required.');
      return;
    }
    if (!form.freeall) {
      if (!form.coupon_type) {
        setFormError('Select a coupon type.');
        return;
      }
      if (!form.coupon_apply_for) {
        setFormError('Select where the coupon applies.');
        return;
      }
      if (form.amt_perc === '' || Number.isNaN(Number(form.amt_perc))) {
        setFormError('Enter a valid amount or percentage.');
        return;
      }
    }

    setFormSaving(true);
    let body: Record<string, unknown>;
    if (form.freeall && editId == null) {
      body = {
        coupon_name: name.toUpperCase(),
        coupon_type: 'percentage',
        coupon_apply_for: 'total',
        amt_perc: 100,
        freeall: true,
      };
    } else {
      body = {
        coupon_name: name.toUpperCase(),
        coupon_type: form.coupon_type,
        coupon_apply_for: form.coupon_apply_for,
        amt_perc: form.amt_perc === '' ? 0 : Number(form.amt_perc),
        freeall: form.freeall,
      };
    }
    if (editId != null) {
      body.status = form.status;
    }

    try {
      if (editId == null) {
        await saasAxios.post('/api/saas/coupons', body);
        showToast('Coupon created.', 'success');
      } else {
        await saasAxios.put(`/api/saas/coupons/${editId}`, body);
        showToast('Coupon updated.', 'success');
      }
      closeModal();
      reload();
    } catch (err: unknown) {
      const ax = err as { response?: { data?: { message?: string } } };
      const msg = ax.response?.data?.message ?? 'Could not save coupon.';
      setFormError(msg);
      showToast(msg, 'default');
    } finally {
      setFormSaving(false);
    }
  }

  const fieldsLocked = form.freeall;

  return (
    <div className="content-wrapper">
      <div className="page-header reveal active">
        <h1 className="page-title">Coupons</h1>
        <div className="toolbar">
          <button type="button" className="top-bar-page-chip top-bar-page-chip--btn" onClick={openCreate}>
            <span className="top-bar-page-chip__shine" aria-hidden />
            <span className="top-bar-page-chip__text">Create Coupon</span>
          </button>
        </div>
      </div>

      <div className="card reveal active">
        <div className="table-responsive">
          <table className="data-table">
            <thead>
              <tr>
                <th>Code</th>
                <th>Discount</th>
                <th>Usage</th>
                <th>Expiry</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {(data?.data ?? []).map((c: any) => {
                const st = couponStatusPill(c);
                return (
                  <tr key={c.id}>
                    <td>
                      <span style={{ fontFamily: 'monospace', fontWeight: 700 }}>
                        {c.coupon ?? '—'}
                      </span>
                    </td>
                    <td>{discountLabel(c)}</td>
                    <td>— / —</td>
                    <td>—</td>
                    <td>
                      <span className={st.cls}>{st.text}</span>
                    </td>
                    <td className="table-actions-cell">
                      <button
                        type="button"
                        className="icon-btn"
                        aria-label="More"
                        aria-expanded={menuId === c.id}
                        onClick={(e) => {
                          e.stopPropagation();
                          setMenuId((id) => (id === c.id ? null : c.id));
                        }}
                      >
                        <MoreHorizontal size={18} />
                      </button>
                      {menuId === c.id ? (
                        <div className="table-actions-menu" role="menu" onClick={(e) => e.stopPropagation()}>
                          <button type="button" role="menuitem" onClick={() => void openEdit(c.id)}>
                            Edit coupon
                          </button>
                        </div>
                      ) : null}
                    </td>
                  </tr>
                );
              })}
              {!loading && Array.isArray(data?.data) && data.data.length === 0 ? (
                <tr>
                  <td colSpan={6}>No coupons.</td>
                </tr>
              ) : null}
              {loading ? (
                <tr>
                  <td colSpan={6} style={{ color: 'var(--text-muted)' }}>
                    Loading…
                  </td>
                </tr>
              ) : null}
            </tbody>
          </table>
        </div>
      </div>

      {modalOpen ? (
        <div
          className="company-edit-modal-backdrop"
          style={{
            position: 'fixed',
            inset: 0,
            zIndex: 200,
            background: 'rgba(15, 23, 42, 0.45)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            padding: 24,
          }}
          role="presentation"
          onMouseDown={(e) => {
            if (e.target === e.currentTarget) closeModal();
          }}
        >
          <div
            className="card"
            style={{ width: '100%', maxWidth: 480, maxHeight: '90vh', overflow: 'auto', position: 'relative' }}
            role="dialog"
            aria-labelledby="coupon-modal-title"
            onMouseDown={(e) => e.stopPropagation()}
          >
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 16 }}>
              <h2 id="coupon-modal-title" className="card-title" style={{ margin: 0 }}>
                {editId == null ? 'Create coupon' : 'Edit coupon'}
              </h2>
              <button type="button" className="icon-btn" aria-label="Close" onClick={closeModal} disabled={formSaving}>
                <X size={20} />
              </button>
            </div>

            {formLoading ? (
              <p style={{ color: 'var(--text-muted)' }}>Loading…</p>
            ) : (
              <>
                {formError ? (
                  <div className="form-group" style={{ color: 'crimson', fontSize: 14, marginBottom: 12 }}>
                    {formError}
                  </div>
                ) : null}

                <div className="form-group">
                  <label className="form-label" htmlFor="cp-name">
                    Coupon code
                  </label>
                  <input
                    id="cp-name"
                    className="form-input"
                    maxLength={10}
                    style={{ textTransform: 'uppercase' }}
                    value={form.coupon_name}
                    onChange={(e) => setForm((f) => ({ ...f, coupon_name: e.target.value }))}
                    disabled={formSaving}
                  />
                </div>

                <div className="form-group" style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                  <input
                    id="cp-free"
                    type="checkbox"
                    checked={form.freeall}
                    onChange={(e) => setForm((f) => ({ ...f, freeall: e.target.checked }))}
                    disabled={formSaving}
                  />
                  <label htmlFor="cp-free" style={{ margin: 0, cursor: 'pointer' }}>
                    100% free (covers full order)
                  </label>
                </div>

                <div className="form-group">
                  <label className="form-label" htmlFor="cp-type">
                    Type
                  </label>
                  <select
                    id="cp-type"
                    className="form-input"
                    value={form.coupon_type}
                    onChange={(e) => setForm((f) => ({ ...f, coupon_type: e.target.value }))}
                    disabled={formSaving || fieldsLocked}
                  >
                    <option value="amount">Amount</option>
                    <option value="percentage">Percentage</option>
                  </select>
                </div>

                <div className="form-group">
                  <label className="form-label" htmlFor="cp-apply">
                    Apply to
                  </label>
                  <select
                    id="cp-apply"
                    className="form-input"
                    value={form.coupon_apply_for}
                    onChange={(e) => setForm((f) => ({ ...f, coupon_apply_for: e.target.value }))}
                    disabled={formSaving || fieldsLocked}
                  >
                    <option value="total">Order total</option>
                    <option value="per-order">Per order</option>
                  </select>
                </div>

                <div className="form-group">
                  <label className="form-label" htmlFor="cp-amt">
                    Amount or % value
                  </label>
                  <input
                    id="cp-amt"
                    type="number"
                    className="form-input"
                    step="any"
                    value={form.amt_perc}
                    onChange={(e) => setForm((f) => ({ ...f, amt_perc: e.target.value }))}
                    disabled={formSaving || fieldsLocked}
                  />
                </div>

                {editId != null ? (
                  <div className="form-group">
                    <label className="form-label" htmlFor="cp-status">
                      Status
                    </label>
                    <select
                      id="cp-status"
                      className="form-input"
                      value={form.status}
                      onChange={(e) =>
                        setForm((f) => ({ ...f, status: e.target.value === '0' ? 0 : 1 }))
                      }
                      disabled={formSaving}
                    >
                      <option value={1}>Active</option>
                      <option value={0}>Inactive</option>
                    </select>
                  </div>
                ) : null}

                <div style={{ display: 'flex', gap: 12, justifyContent: 'flex-end', marginTop: 20 }}>
                  <button type="button" className="btn btn-outline" onClick={closeModal} disabled={formSaving}>
                    Cancel
                  </button>
                  <button type="button" className="btn btn-primary" onClick={() => void saveCoupon()} disabled={formSaving}>
                    {formSaving ? 'Saving…' : 'Save'}
                  </button>
                </div>
              </>
            )}
          </div>
        </div>
      ) : null}
    </div>
  );
}
