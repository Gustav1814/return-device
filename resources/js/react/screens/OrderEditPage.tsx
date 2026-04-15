import { useEffect, useMemo, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { ArrowLeft } from 'lucide-react';
import { saasAxios } from '../api/saasAxios';
import { useToast } from '../context/ToastContext';
import { useSaasMe } from '../context/SaasMeContext';
import { US_STATES } from '../data/usStates';

type EditForm = {
  equipment_type: 'Laptop' | 'Monitor';
  emp_firstname: string;
  emp_lastname: string;
  emp_email: string;
  emp_phone: string;
  emp_add1: string;
  emp_add2: string;
  emp_city: string;
  emp_state: string;
  emp_pcode: string;
  custom_msg: string;
  return_srv: '1' | '2';
  comp_name: string;
  comp_rec_person: string;
  comp_email: string;
  comp_phone: string;
  comp_add_1: string;
  comp_add_2: string;
  comp_city: string;
  comp_state: string;
  comp_zip: string;
  ins_tick: number;
  ins_amount: string;
};

function formFromDetail(d: any): EditForm {
  return {
    equipment_type: d?.type_of_equip === 'Monitor' ? 'Monitor' : 'Laptop',
    emp_firstname: d?.emp_first_name ?? '',
    emp_lastname: d?.emp_last_name ?? '',
    emp_email: d?.emp_email ?? '',
    emp_phone: d?.emp_phone ?? '',
    emp_add1: d?.emp_add_1 ?? '',
    emp_add2: d?.emp_add_2 ?? '',
    emp_city: d?.emp_city ?? '',
    emp_state: d?.emp_state ?? '',
    emp_pcode: d?.emp_pcode ?? '',
    custom_msg: d?.custom_msg ?? '',
    return_srv: d?.return_service === 'Sell This Equipment' ? '2' : '1',
    comp_name: d?.receipient_name ?? '',
    comp_rec_person: d?.receipient_person ?? '',
    comp_email: d?.receipient_email ?? '',
    comp_phone: d?.receipient_phone ?? '',
    comp_add_1: d?.receipient_add_1 ?? '',
    comp_add_2: d?.receipient_add_2 ?? '',
    comp_city: d?.receipient_city ?? '',
    comp_state: d?.receipient_state ?? '',
    comp_zip: d?.receipient_zip ?? '',
    ins_tick: Number(d?.insurance_active ?? 0) ? 1 : 0,
    ins_amount: d?.insurance_amount != null ? String(d.insurance_amount) : '',
  };
}

export function OrderEditPage() {
  const { itemId } = useParams();
  const navigate = useNavigate();
  const { showToast } = useToast();
  const me = useSaasMe();
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState<EditForm | null>(null);

  const isAdmin = me?.is_rr_company === true;

  useEffect(() => {
    if (me && !isAdmin) {
      navigate('/orders/in-progress', { replace: true });
      showToast('Only admin can edit orders.', 'error');
    }
  }, [isAdmin, me, navigate, showToast]);

  useEffect(() => {
    if (!itemId) return;
    setLoading(true);
    saasAxios
      .get(`/api/saas/orders/${itemId}`)
      .then((r) => setForm(formFromDetail(r.data)))
      .catch(() => showToast('Could not load order.', 'error'))
      .finally(() => setLoading(false));
  }, [itemId, showToast]);

  const title = useMemo(() => (itemId ? `Edit Order #${itemId}` : 'Edit Order'), [itemId]);

  function setField<K extends keyof EditForm>(key: K, value: EditForm[K]) {
    setForm((prev) => (prev ? { ...prev, [key]: value } : prev));
  }

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    if (!itemId || !form) return;
    setSaving(true);
    try {
      const { data } = await saasAxios.post(`/sub-order/edit/${itemId}`, form, {
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      });
      if (data?.status === 'success') {
        showToast(data.message ?? 'Order updated.', 'success');
        navigate(`/orders/${itemId}`, { replace: true });
        return;
      }
      showToast(data?.message ?? 'Could not update order.', 'error');
    } catch (err: unknown) {
      const ax = err as { response?: { data?: { message?: string } } };
      showToast(ax.response?.data?.message ?? 'Could not update order.', 'error');
    } finally {
      setSaving(false);
    }
  }

  if (loading || !form) {
    return (
      <div className="content-wrapper">
        <div className="card reveal active">Loading…</div>
      </div>
    );
  }

  return (
    <div className="content-wrapper">
      <div className="page-header reveal active">
        <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
          <Link to="/orders/in-progress" className="icon-btn" aria-label="Back">
            <ArrowLeft size={18} />
          </Link>
          <h1 className="page-title">{title}</h1>
        </div>
      </div>

      <div className="card reveal active">
        <form onSubmit={submit}>
          <h3 className="card-title" style={{ marginBottom: 16 }}>Type of Equipment</h3>
          <div className="form-group" style={{ maxWidth: 380 }}>
            <select
              className="form-input"
              value={form.equipment_type}
              onChange={(e) => setField('equipment_type', e.target.value as EditForm['equipment_type'])}
            >
              <option value="Laptop">Laptop</option>
              <option value="Monitor">Monitor</option>
            </select>
          </div>

          <h3 className="card-title" style={{ marginBottom: 16, marginTop: 12 }}>Employee Address</h3>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: 12 }}>
            <input className="form-input" placeholder="First Name" value={form.emp_firstname} onChange={(e) => setField('emp_firstname', e.target.value)} />
            <input className="form-input" placeholder="Last Name" value={form.emp_lastname} onChange={(e) => setField('emp_lastname', e.target.value)} />
            <input className="form-input" placeholder="Email" value={form.emp_email} onChange={(e) => setField('emp_email', e.target.value)} />
            <input className="form-input" placeholder="Phone" value={form.emp_phone} onChange={(e) => setField('emp_phone', e.target.value)} />
            <input className="form-input" placeholder="Address 1" value={form.emp_add1} onChange={(e) => setField('emp_add1', e.target.value)} />
            <input className="form-input" placeholder="Address 2" value={form.emp_add2} onChange={(e) => setField('emp_add2', e.target.value)} />
            <input className="form-input" placeholder="City" value={form.emp_city} onChange={(e) => setField('emp_city', e.target.value)} />
            <select className="form-input" value={form.emp_state} onChange={(e) => setField('emp_state', e.target.value)}>
              <option value="">State</option>
              {US_STATES.map((s) => (
                <option key={s.code} value={s.code}>{s.name}</option>
              ))}
            </select>
            <input className="form-input" placeholder="Zip/Postal code" value={form.emp_pcode} onChange={(e) => setField('emp_pcode', e.target.value)} />
          </div>
          <div className="form-group" style={{ marginTop: 12 }}>
            <textarea className="form-input" placeholder="Custom message" value={form.custom_msg} onChange={(e) => setField('custom_msg', e.target.value)} />
          </div>

          <h3 className="card-title" style={{ marginBottom: 16, marginTop: 18 }}>Return Destination</h3>
          <div className="form-group" style={{ maxWidth: 380 }}>
            <select className="form-input" value={form.return_srv} onChange={(e) => setField('return_srv', e.target.value as '1' | '2')}>
              <option value="1">Return To Company</option>
              <option value="2">Recycle with Data Destruction</option>
            </select>
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: 12 }}>
            <input className="form-input" placeholder="Company Name" value={form.comp_name} onChange={(e) => setField('comp_name', e.target.value)} />
            <input className="form-input" placeholder="Company Email" value={form.comp_email} onChange={(e) => setField('comp_email', e.target.value)} />
            <input className="form-input" placeholder="Recipient Name" value={form.comp_rec_person} onChange={(e) => setField('comp_rec_person', e.target.value)} />
            <input className="form-input" placeholder="Company Phone" value={form.comp_phone} onChange={(e) => setField('comp_phone', e.target.value)} />
            <input className="form-input" placeholder="Company Address 1" value={form.comp_add_1} onChange={(e) => setField('comp_add_1', e.target.value)} />
            <input className="form-input" placeholder="Company Address 2" value={form.comp_add_2} onChange={(e) => setField('comp_add_2', e.target.value)} />
            <input className="form-input" placeholder="Company City" value={form.comp_city} onChange={(e) => setField('comp_city', e.target.value)} />
            <select className="form-input" value={form.comp_state} onChange={(e) => setField('comp_state', e.target.value)}>
              <option value="">State</option>
              {US_STATES.map((s) => (
                <option key={s.code} value={s.code}>{s.name}</option>
              ))}
            </select>
            <input className="form-input" placeholder="Zip/Postal code" value={form.comp_zip} onChange={(e) => setField('comp_zip', e.target.value)} />
          </div>

          <div style={{ marginTop: 18, display: 'flex', gap: 10 }}>
            <button className="btn btn-primary" type="submit" disabled={saving}>
              {saving ? 'Updating…' : 'Update Order'}
            </button>
            <Link className="btn btn-outline" to={`/orders/${itemId}`}>Cancel</Link>
          </div>
        </form>
      </div>
    </div>
  );
}

