import { useEffect, useMemo, useState } from 'react';
import { saasAxios } from '../api/saasAxios';
import { useToast } from '../context/ToastContext';

type PriceRow = {
  id?: number;
  equipment_type: string;
  order_amount: number | string;
  min_order_amount?: number | null;
};

export function SettingsPricesPage() {
  const { showToast } = useToast();
  const [rows, setRows] = useState<PriceRow[]>([]);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    saasAxios
      .get('/api/saas/prices')
      .then((r) => setRows(Array.isArray(r.data) ? r.data : []))
      .catch(() => setRows([]));
  }, []);

  const totals = useMemo(() => {
    return rows.map((r) => {
      const base = Number(r.order_amount ?? 0);
      return Number.isFinite(base) ? base : 0;
    });
  }, [rows]);

  async function save() {
    const tooLow = rows.find((r) => {
      const min = Number(r.min_order_amount ?? NaN);
      if (!Number.isFinite(min)) return false;
      const amount = Number(r.order_amount);
      return Number.isFinite(amount) && amount < min;
    });
    if (tooLow) {
      const min = Number(tooLow.min_order_amount ?? 0).toFixed(2);
      showToast(
        `${tooLow.equipment_type} cannot be below admin base fee $${min}. You can keep it same or increase.`,
        'error',
      );
      return;
    }

    setSaving(true);
    try {
      const payload = {
        prices: rows.map((r) => ({
          equipment_type: r.equipment_type,
          order_amount: Number(r.order_amount),
        })),
      };
      const { data: updated } = await saasAxios.put('/api/saas/prices', payload);
      setRows(Array.isArray(updated) ? updated : rows);
      showToast('Prices updated.', 'success');
    } catch (error: any) {
      const responseData = error?.response?.data;
      const validationErrors = responseData?.errors;
      let apiMessage = '';

      // Prefer field-level validation errors (more specific than generic "The given data was invalid.").
      if (validationErrors && typeof validationErrors === 'object') {
        for (const value of Object.values(validationErrors as Record<string, unknown>)) {
          if (Array.isArray(value) && value.length > 0 && typeof value[0] === 'string') {
            apiMessage = value[0];
            break;
          }
          if (typeof value === 'string' && value.trim() !== '') {
            apiMessage = value;
            break;
          }
        }
      }

      if (!apiMessage && typeof responseData?.message === 'string') {
        apiMessage = responseData.message;
      }
      if (!apiMessage && typeof error?.message === 'string') {
        apiMessage = error.message;
      }

      showToast(
        typeof apiMessage === 'string' && apiMessage.trim() !== ''
          ? apiMessage
          : 'Could not save prices. Check values and try again.',
        'error',
      );
    } finally {
      setSaving(false);
    }
  }

  return (
    <div className="content-wrapper">
      <div className="page-header reveal active">
        <h1 className="page-title">Price Settings</h1>
        <div className="toolbar">
          <button
            className="top-bar-page-chip top-bar-page-chip--btn"
            type="button"
            onClick={save}
            disabled={saving}
          >
            <span className="top-bar-page-chip__shine" aria-hidden />
            <span className="top-bar-page-chip__text">{saving ? 'Saving…' : 'Update Prices'}</span>
          </button>
        </div>
      </div>

      <div className="card reveal active">
        <h3 className="card-title" style={{ marginBottom: 24 }}>
          Equipment Return Fees
        </h3>
        <div className="table-responsive">
          <table className="data-table">
            <thead>
              <tr>
                <th>Equipment Type</th>
                <th>Base Fee</th>
                <th>Admin Min Base Fee</th>
                <th>Total Estimate</th>
              </tr>
            </thead>
            <tbody>
              {rows.map((r, idx) => (
                <tr key={r.id ?? r.equipment_type}>
                  <td>
                    <strong>{r.equipment_type}</strong>
                  </td>
                  <td>
                    <input
                      type="text"
                      className="form-input"
                      value={r.order_amount ?? ''}
                      onChange={(e) => {
                        const next = [...rows];
                        next[idx] = { ...next[idx], order_amount: e.target.value };
                        setRows(next);
                      }}
                      style={{ width: 100 }}
                    />
                  </td>
                  <td>
                    {Number.isFinite(Number(r.min_order_amount))
                      ? `$${Number(r.min_order_amount).toFixed(2)}`
                      : '—'}
                  </td>
                  <td>${totals[idx]?.toFixed(2) ?? '0.00'}</td>
                </tr>
              ))}
              {rows.length === 0 ? (
                <tr>
                  <td colSpan={4}>No price rows found for this company.</td>
                </tr>
              ) : null}
            </tbody>
          </table>
        </div>
        <p style={{ marginTop: 16, fontSize: 12, color: 'var(--text-muted)' }}>
          * Companies can set base fee equal to or above admin minimum, but not below it.
        </p>
      </div>
    </div>
  );
}
