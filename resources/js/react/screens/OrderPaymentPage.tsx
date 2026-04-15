import { useEffect, useMemo, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { saasAxios } from '../api/saasAxios';

export function OrderPaymentPage() {
  const { itemId } = useParams();
  const navigate = useNavigate();
  const [detail, setDetail] = useState<any>(null);
  const [form, setForm] = useState({
    oid: '',
    cc_no: '',
    cvv: '',
    cc_month: '',
    cc_year: '',
    cardholder_name: '',
    comp_city: '',
    comp_state: '',
    comp_zip: '',
  });
  const [msg, setMsg] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (!itemId) return;
    saasAxios
      .get(`/api/saas/orders/${itemId}`)
      .then((r) => {
        setDetail(r.data);
        setForm((f) => ({
          ...f,
          oid: String(r.data?.order_id ?? ''),
        }));
      })
      .catch(() => setDetail(null));
  }, [itemId]);

  const baseFee = useMemo(() => {
    const v = Number(detail?.order_amt ?? 0);
    return Number.isFinite(v) ? v : 0;
  }, [detail]);

  const insurance = useMemo(() => {
    const v = Number(detail?.insurance_amount ?? 0);
    return Number.isFinite(v) ? v : 0;
  }, [detail]);

  const total = useMemo(() => baseFee + insurance, [baseFee, insurance]);

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setSaving(true);
    setMsg(null);
    try {
      const { data: d, status } = await saasAxios.post('/payment-sub', {
        ...form,
        oid: form.oid,
      });
      setMsg(
        (d as { message?: string })?.message ??
          (status >= 200 && status < 300 ? 'Success' : 'Payment failed'),
      );
      if ((d as { status?: string })?.status === 'success') {
        navigate(`/orders/${itemId}`, { replace: true });
      }
    } catch (err: unknown) {
      const ax = err as { response?: { data?: { message?: string } } };
      setMsg(ax.response?.data?.message ?? 'Payment failed');
    } finally {
      setSaving(false);
    }
  }

  if (!detail) {
    return (
      <div className="content-wrapper">
        <div style={{ maxWidth: 600, margin: '40px auto' }} className="card reveal active">
          Loading…
        </div>
      </div>
    );
  }

  return (
    <div className="content-wrapper">
      <div style={{ maxWidth: 600, margin: '40px auto' }}>
        <div className="card reveal active">
          <h1 className="page-title" style={{ marginBottom: 24, textAlign: 'center' }}>
            Order Summary
          </h1>

          <div
            style={{
              borderBottom: '1px solid var(--border-color)',
              paddingBottom: 20,
              marginBottom: 20,
            }}
          >
            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 12 }}>
              <span style={{ color: 'var(--text-muted)' }}>Order ID</span>
              <span style={{ fontWeight: 600 }}>
                {detail.order_id ? `#ORD-${detail.order_id}` : `#${detail.id}`}
              </span>
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 12 }}>
              <span style={{ color: 'var(--text-muted)' }}>Equipment</span>
              <span style={{ fontWeight: 600 }}>{detail.type_of_equip ?? '—'}</span>
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
              <span style={{ color: 'var(--text-muted)' }}>Shipping Label</span>
              <span style={{ fontWeight: 600 }}>{detail.return_service ?? 'Standard'}</span>
            </div>
          </div>

          <form onSubmit={submit}>
            <div style={{ marginBottom: 32 }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 12 }}>
                <span>Base Fee</span>
                <span>${baseFee.toFixed(2)}</span>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 12 }}>
                <span>Insurance</span>
                <span>${insurance.toFixed(2)}</span>
              </div>
              <div
                style={{
                  display: 'flex',
                  justifyContent: 'space-between',
                  fontSize: 20,
                  fontWeight: 700,
                  marginTop: 20,
                  paddingTop: 20,
                  borderTop: '2px solid var(--border-color)',
                }}
              >
                <span>Total Amount</span>
                <span style={{ color: 'var(--brand-primary)' }}>${total.toFixed(2)}</span>
              </div>
            </div>

            <details style={{ marginBottom: 20 }}>
              <summary style={{ cursor: 'pointer', fontSize: 14, color: 'var(--text-muted)' }}>
                Billing &amp; card details
              </summary>
              <div style={{ display: 'grid', gap: 12, marginTop: 16 }}>
                <input type="hidden" name="oid" value={form.oid} readOnly />
                <div className="form-group">
                  <label className="form-label">Order ID (oid)</label>
                  <input
                    className="form-input"
                    value={form.oid}
                    onChange={(e) => setForm({ ...form, oid: e.target.value })}
                    required
                  />
                </div>
                <div className="form-group">
                  <label className="form-label">Cardholder name</label>
                  <input
                    className="form-input"
                    value={form.cardholder_name}
                    onChange={(e) => setForm({ ...form, cardholder_name: e.target.value })}
                    required
                  />
                </div>
                <div className="form-group">
                  <label className="form-label">Card number</label>
                  <input
                    className="form-input"
                    value={form.cc_no}
                    onChange={(e) => setForm({ ...form, cc_no: e.target.value })}
                    required
                  />
                </div>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 12 }}>
                  <div className="form-group">
                    <label className="form-label">Month</label>
                    <input
                      className="form-input"
                      value={form.cc_month}
                      onChange={(e) => setForm({ ...form, cc_month: e.target.value })}
                      required
                    />
                  </div>
                  <div className="form-group">
                    <label className="form-label">Year</label>
                    <input
                      className="form-input"
                      value={form.cc_year}
                      onChange={(e) => setForm({ ...form, cc_year: e.target.value })}
                      required
                    />
                  </div>
                  <div className="form-group">
                    <label className="form-label">CVV</label>
                    <input
                      className="form-input"
                      value={form.cvv}
                      onChange={(e) => setForm({ ...form, cvv: e.target.value })}
                      required
                    />
                  </div>
                </div>
                <div className="form-group">
                  <label className="form-label">City</label>
                  <input
                    className="form-input"
                    value={form.comp_city}
                    onChange={(e) => setForm({ ...form, comp_city: e.target.value })}
                    required
                  />
                </div>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                  <div className="form-group">
                    <label className="form-label">State</label>
                    <input
                      className="form-input"
                      value={form.comp_state}
                      onChange={(e) => setForm({ ...form, comp_state: e.target.value })}
                      required
                    />
                  </div>
                  <div className="form-group">
                    <label className="form-label">ZIP</label>
                    <input
                      className="form-input"
                      value={form.comp_zip}
                      onChange={(e) => setForm({ ...form, comp_zip: e.target.value })}
                      required
                    />
                  </div>
                </div>
              </div>
            </details>

            {msg ? (
              <div style={{ marginBottom: 16, fontSize: 14 }} role="status">
                {msg}
              </div>
            ) : null}

            <button
              type="submit"
              className="btn btn-primary"
              style={{ width: '100%', height: 50, fontSize: 16 }}
              disabled={saving}
            >
              {saving ? 'Processing…' : 'Pay with Stripe'}
            </button>
          </form>

          <div style={{ textAlign: 'center', marginTop: 20 }}>
            <Link to="/orders/new" style={{ fontSize: 14, color: 'var(--text-muted)' }}>
              Cancel and return
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
