import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Download, UploadCloud } from 'lucide-react';
import { saasAxios } from '../api/saasAxios';
import { getLaravelBaseUrl } from '../runtimeBase';
import { useToast } from '../context/ToastContext';

export function OrderCreatePage() {
  const navigate = useNavigate();
  const { showToast } = useToast();
  const [customerName, setCustomerName] = useState('');
  const [customerEmail, setCustomerEmail] = useState('');
  const [equipment, setEquipment] = useState('Laptop');
  const [serial, setSerial] = useState('');
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setError(null);
    setSaving(true);
    const parts = customerName.trim().split(/\s+/);
    const emp_first_name = parts[0] ?? '';
    const emp_last_name = parts.slice(1).join(' ') || parts[0] || '';

    const payload = {
      type_of_equip: equipment,
      return_service: 'Return To Company',
      receipient_name: customerName.trim(),
      receipient_email: customerEmail.trim(),
      emp_first_name,
      emp_last_name,
      emp_email: customerEmail.trim(),
      custom_msg: serial.trim() ? `Serial: ${serial.trim()}` : undefined,
    };

    try {
      const { data: d } = await saasAxios.post('/api/saas/orders', payload);
      navigate(`/orders/${d.item_id}/payment`, { replace: true });
    } catch (err: unknown) {
      const ax = err as { response?: { data?: { message?: string } } };
      setError(ax.response?.data?.message ?? 'Failed to create order.');
    } finally {
      setSaving(false);
    }
  }

  function goBulkImport() {
    navigate('/orders/bulk');
  }

  return (
    <div className="content-wrapper">
      <div className="page-header reveal active">
        <h1 className="page-title">Create Order</h1>
      </div>

      <div className="dashboard-row">
        <div className="card reveal active">
          <h3 className="card-title" style={{ marginBottom: 24 }}>
            Single Device Return
          </h3>
          {error ? (
            <div className="form-group" style={{ color: 'crimson', fontSize: 14 }}>
              {error}
            </div>
          ) : null}
          <form id="create-order-form" onSubmit={submit}>
            <div className="form-group">
              <label className="form-label">Customer Name</label>
              <input
                type="text"
                className="form-input"
                placeholder="e.g. John Doe"
                required
                value={customerName}
                onChange={(e) => setCustomerName(e.target.value)}
              />
            </div>
            <div className="form-group">
              <label className="form-label">Customer Email</label>
              <input
                type="email"
                className="form-input"
                placeholder="john@example.com"
                required
                value={customerEmail}
                onChange={(e) => setCustomerEmail(e.target.value)}
              />
            </div>
            <div className="form-group">
              <label className="form-label">Equipment Type</label>
              <select
                className="form-input"
                value={equipment}
                onChange={(e) => setEquipment(e.target.value)}
              >
                <option>Laptop</option>
                <option>Monitor</option>
                <option>Tablet</option>
                <option>Other</option>
              </select>
            </div>
            <div className="form-group">
              <label className="form-label">Serial Number (Optional)</label>
              <input
                type="text"
                className="form-input"
                placeholder="SN-XXXX-XXXX"
                value={serial}
                onChange={(e) => setSerial(e.target.value)}
              />
            </div>
            <button
              type="submit"
              className="btn btn-primary"
              style={{ width: '100%' }}
              disabled={saving}
            >
              {saving ? 'Creating…' : 'Create & Generate Label'}
            </button>
          </form>
        </div>

        <div className="card reveal active">
          <h3 className="card-title" style={{ marginBottom: 24 }}>
            Bulk Import
          </h3>
          <button
            type="button"
            onClick={goBulkImport}
            style={{
              width: '100%',
              textAlign: 'center',
              padding: '40px 20px',
              border: '2px dashed var(--border-color)',
              borderRadius: 'var(--radius-md)',
              background: 'transparent',
              cursor: 'pointer',
              font: 'inherit',
              color: 'inherit',
            }}
          >
            <UploadCloud
              style={{
                width: 48,
                height: 48,
                color: 'var(--text-muted)',
                marginBottom: 16,
                marginLeft: 'auto',
                marginRight: 'auto',
                display: 'block',
              }}
            />
            <p style={{ fontSize: 14, marginBottom: 8 }}>Import many returns from a CSV file</p>
            <p style={{ fontSize: 12, color: 'var(--text-muted)', marginBottom: 16 }}>
              Upload a CSV in the dashboard — same server processing as before.
            </p>
            <span className="btn btn-outline" style={{ pointerEvents: 'none' }}>
              Open bulk import
            </span>
          </button>
          <div style={{ marginTop: 16, display: 'flex', flexWrap: 'wrap', gap: 12, alignItems: 'center' }}>
            <button type="button" className="btn btn-outline" onClick={goBulkImport}>
              Select CSV file
            </button>
            <a
              href={`${getLaravelBaseUrl()}/download-filecsv`}
              style={{ fontSize: 13, color: 'var(--brand-primary)', textDecoration: 'none', fontWeight: 500 }}
            >
              <Download style={{ width: 14, height: 14, display: 'inline', verticalAlign: 'middle' }} />{' '}
              Download CSV template
            </a>
          </div>
        </div>
      </div>
    </div>
  );
}
