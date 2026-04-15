import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Edit } from 'lucide-react';
import { saasAxios } from '../api/saasAxios';
import { useToast } from '../context/ToastContext';

type PartnerRow = {
  partner: string;
  rate: string;
  total_earned: number | null;
  last_payout: string | null;
};

export function CommissionsPage() {
  const navigate = useNavigate();
  const { showToast } = useToast();
  const [rows, setRows] = useState<PartnerRow[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    setLoading(true);
    saasAxios
      .get('/api/saas/commissions/partners')
      .then((r) => setRows(Array.isArray(r.data?.data) ? r.data.data : []))
      .catch(() => setRows([]))
      .finally(() => setLoading(false));
  }, []);

  function editRates() {
    showToast('Equipment pricing affects commission calculations.', 'default');
    navigate('/settings/prices');
  }

  return (
    <div className="content-wrapper">
      <div className="page-header reveal active">
        <h1 className="page-title">Commission Settings</h1>
        <div className="toolbar">
          <button type="button" className="btn btn-outline" onClick={editRates}>
            <Edit size={18} /> Price settings
          </button>
        </div>
      </div>

      <div className="card reveal active">
        <div className="table-responsive">
          <table className="data-table">
            <thead>
              <tr>
                <th>Partner</th>
                <th>Commission Rate</th>
                <th>Total Earned</th>
                <th>Last Payout</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {rows.map((r, i) => (
                <tr key={`${r.partner}-${i}`}>
                  <td>{r.partner}</td>
                  <td>{r.rate}</td>
                  <td>{r.total_earned != null ? `$${Number(r.total_earned).toFixed(2)}` : '—'}</td>
                  <td>{r.last_payout ? String(r.last_payout).slice(0, 10) : '—'}</td>
                  <td>
                    <button type="button" className="icon-btn" aria-label="Edit pricing" onClick={editRates}>
                      <Edit size={18} />
                    </button>
                  </td>
                </tr>
              ))}
              {!loading && rows.length === 0 ? (
                <tr>
                  <td colSpan={5}>No commission rows.</td>
                </tr>
              ) : null}
              {loading ? (
                <tr>
                  <td colSpan={5} style={{ color: 'var(--text-muted)' }}>
                    Loading…
                  </td>
                </tr>
              ) : null}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
