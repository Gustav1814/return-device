import { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { ArrowLeft, Pencil, Save, X } from 'lucide-react';
import { saasAxios } from '../api/saasAxios';
import { useToast } from '../context/ToastContext';

type CompanyDetailResponse = {
  id: number;
  company_name?: string | null;
  domain?: string | null;
  company_domain?: string | null;
  receipient_name?: string | null;
  company_email?: string | null;
  company_add_1?: string | null;
  company_add_2?: string | null;
  company_city?: string | null;
  company_state?: string | null;
  company_zip?: string | null;
  company_phone?: string | null;
  user_status?: string;
  created_at?: string | null;
  main_domain?: string | null;
  portal_url?: string | null;
  user?: { name?: string | null; email?: string | null; phone?: string | null } | null;
};

function formatCreatedAt(iso: string | null | undefined) {
  if (!iso) return '—';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return iso.slice(0, 10);
  return d.toLocaleDateString(undefined, { month: 'short', day: '2-digit', year: 'numeric' });
}

export function CompanyDetailPage() {
  const { companyId } = useParams<{ companyId: string }>();
  const id = companyId && /^\d+$/.test(companyId) ? Number(companyId) : NaN;
  const navigate = useNavigate();
  const { showToast } = useToast();

  const [row, setRow] = useState<CompanyDetailResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [statusDraft, setStatusDraft] = useState<'active' | 'inactive'>('active');
  const [statusSaving, setStatusSaving] = useState(false);

  const [domainEdit, setDomainEdit] = useState(false);
  const [domainDraft, setDomainDraft] = useState('');
  const [domainSaving, setDomainSaving] = useState(false);

  const load = useCallback(async () => {
    if (!Number.isFinite(id) || id <= 0) {
      setError('Invalid company.');
      setLoading(false);
      return;
    }
    setLoading(true);
    setError(null);
    try {
      const { data } = await saasAxios.get<CompanyDetailResponse>(`/api/saas/companies/${id}`);
      setRow(data);
      setStatusDraft(data.user_status === 'inactive' ? 'inactive' : 'active');
      setDomainDraft(String(data.company_domain ?? '').replace(/\s/g, '').toLowerCase());
    } catch (e: unknown) {
      const ax = e as { response?: { data?: { message?: string } } };
      setError(ax.response?.data?.message ?? 'Could not load company.');
      setRow(null);
    } finally {
      setLoading(false);
    }
  }, [id]);

  useEffect(() => {
    void load();
  }, [load]);

  async function submitStatus() {
    if (!Number.isFinite(id) || id <= 0) return;
    setStatusSaving(true);
    try {
      const { data } = await saasAxios.post<{ status?: string; message?: string }>(
        `/api/saas/companies/${id}/user-status`,
        { status: statusDraft },
      );
      if (data?.status === 'success') {
        showToast(data.message ?? 'Status updated.', 'success');
        await load();
      } else {
        showToast(data?.message ?? 'Update failed.', 'default');
      }
    } catch (e: unknown) {
      const ax = e as { response?: { data?: { message?: string } } };
      showToast(ax.response?.data?.message ?? 'Could not update status.', 'error');
    } finally {
      setStatusSaving(false);
    }
  }

  async function submitDomain() {
    if (!Number.isFinite(id) || id <= 0) return;
    const slug = domainDraft.replace(/\s/g, '').toLowerCase();
    if (!slug) {
      showToast('Enter a domain slug.', 'default');
      return;
    }
    setDomainSaving(true);
    try {
      const { data } = await saasAxios.patch<{ status?: string; message?: string; company_domain?: string }>(
        `/api/saas/companies/${id}/domain`,
        { company_domain: slug },
      );
      if (data?.status === 'success') {
        showToast(data.message ?? 'Domain updated.', 'success');
        setDomainEdit(false);
        await load();
      } else {
        showToast(data?.message ?? 'Update failed.', 'default');
      }
    } catch (e: unknown) {
      const ax = e as { response?: { data?: { message?: string } } };
      showToast(ax.response?.data?.message ?? 'Could not update domain.', 'error');
    } finally {
      setDomainSaving(false);
    }
  }

  if (!Number.isFinite(id) || id <= 0) {
    return (
      <div className="content-wrapper">
        <p style={{ color: 'crimson' }}>Invalid company.</p>
        <Link to="/companies" className="btn btn-outline btn-sm">
          Back to companies
        </Link>
      </div>
    );
  }

  return (
    <div className="content-wrapper">
      <div className="page-header reveal active" style={{ marginBottom: 20 }}>
        <div style={{ display: 'flex', flexWrap: 'wrap', alignItems: 'center', gap: 16 }}>
          <Link
            to="/companies"
            className="btn btn-outline btn-sm"
            style={{ display: 'inline-flex', alignItems: 'center', gap: 8 }}
          >
            <ArrowLeft size={16} />
            Back to companies
          </Link>
          <h1 className="page-title" style={{ margin: 0, flex: '1 1 auto' }}>
            Company detail
          </h1>
        </div>
      </div>

      {loading ? (
        <div className="card reveal active" style={{ padding: 28 }}>
          <p style={{ color: 'var(--text-muted)', margin: 0 }}>Loading…</p>
        </div>
      ) : null}

      {error && !loading ? (
        <div className="card reveal active" style={{ padding: 28 }}>
          <p style={{ color: 'crimson', marginBottom: 16 }}>{error}</p>
          <button type="button" className="btn btn-outline btn-sm" onClick={() => navigate('/companies')}>
            Back to companies
          </button>
        </div>
      ) : null}

      {row && !loading ? (
        <>
          <div
            className="card reveal active"
            style={{
              marginBottom: 20,
              padding: '24px 28px',
              background: 'linear-gradient(135deg, rgba(var(--brand-primary-rgb), 0.08) 0%, rgba(var(--brand-accent-rgb), 0.05) 100%)',
              border: '1px solid rgba(var(--brand-primary-rgb), 0.15)',
            }}
          >
            <div
              style={{
                display: 'grid',
                gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
                gap: 24,
                alignItems: 'start',
              }}
            >
              <div>
                <div style={{ fontSize: 11, fontWeight: 700, textTransform: 'uppercase', color: 'var(--text-muted)' }}>
                  Company name
                </div>
                <div style={{ fontSize: 18, fontWeight: 700, marginTop: 6 }}>{row.company_name ?? '—'}</div>
              </div>
              <div>
                <div style={{ fontSize: 11, fontWeight: 700, textTransform: 'uppercase', color: 'var(--text-muted)' }}>
                  Company domain
                </div>
                {!domainEdit ? (
                  <div style={{ marginTop: 6, display: 'flex', flexWrap: 'wrap', alignItems: 'center', gap: 10 }}>
                    {row.portal_url ? (
                      <a href={row.portal_url} target="_blank" rel="noreferrer" style={{ fontWeight: 600 }}>
                        {row.company_domain ?? '—'}
                      </a>
                    ) : (
                      <span style={{ fontWeight: 600 }}>{row.company_domain ?? '—'}</span>
                    )}
                    <button
                      type="button"
                      className="btn btn-outline btn-sm"
                      style={{ display: 'inline-flex', alignItems: 'center', gap: 6 }}
                      onClick={() => {
                        setDomainDraft(String(row.company_domain ?? '').replace(/\s/g, '').toLowerCase());
                        setDomainEdit(true);
                      }}
                    >
                      <Pencil size={14} /> Edit
                    </button>
                  </div>
                ) : (
                  <div style={{ marginTop: 8, display: 'flex', flexWrap: 'wrap', gap: 8, alignItems: 'center' }}>
                    <input
                      className="form-input"
                      style={{ maxWidth: 220 }}
                      value={domainDraft}
                      onChange={(e) => setDomainDraft(e.target.value.replace(/\s/g, '').toLowerCase())}
                      aria-label="Company domain slug"
                    />
                    <button
                      type="button"
                      className="btn btn-primary btn-sm"
                      disabled={domainSaving}
                      style={{ display: 'inline-flex', alignItems: 'center', gap: 6 }}
                      onClick={() => void submitDomain()}
                    >
                      <Save size={14} /> {domainSaving ? 'Saving…' : 'Save'}
                    </button>
                    <button
                      type="button"
                      className="btn btn-outline btn-sm"
                      disabled={domainSaving}
                      onClick={() => {
                        setDomainEdit(false);
                        setDomainDraft(String(row.company_domain ?? '').replace(/\s/g, '').toLowerCase());
                      }}
                    >
                      <X size={14} aria-hidden /> Cancel
                    </button>
                  </div>
                )}
                {row.main_domain ? (
                  <p style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 8, marginBottom: 0 }}>
                    Host base: <code style={{ fontSize: 12 }}>{row.main_domain}</code>
                  </p>
                ) : null}
              </div>
              <div>
                <div style={{ fontSize: 11, fontWeight: 700, textTransform: 'uppercase', color: 'var(--text-muted)' }}>
                  Account status
                </div>
                <div style={{ marginTop: 8 }}>
                  <span
                    className={[
                      'status-pill',
                      row.user_status === 'inactive' ? 'status-pending' : 'status-completed',
                    ].join(' ')}
                  >
                    {row.user_status === 'inactive' ? 'INACTIVE' : 'ACTIVE'}
                  </span>
                </div>
              </div>
              <div>
                <div style={{ fontSize: 11, fontWeight: 700, textTransform: 'uppercase', color: 'var(--text-muted)' }}>
                  Update status
                </div>
                <div style={{ marginTop: 8, display: 'flex', flexWrap: 'wrap', gap: 8, alignItems: 'center' }}>
                  <select
                    className="form-input"
                    style={{ width: 'auto', minWidth: 140 }}
                    value={statusDraft}
                    onChange={(e) => setStatusDraft(e.target.value === 'inactive' ? 'inactive' : 'active')}
                  >
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                  <button
                    type="button"
                    className="btn btn-primary btn-sm"
                    disabled={statusSaving || statusDraft === (row.user_status === 'inactive' ? 'inactive' : 'active')}
                    onClick={() => {
                      if (
                        !window.confirm(
                          "Update this company's user login status? Active users can sign in; inactive users cannot.",
                        )
                      ) {
                        return;
                      }
                      void submitStatus();
                    }}
                  >
                    {statusSaving ? 'Saving…' : 'Apply'}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div className="card reveal active" style={{ padding: '28px 32px' }}>
            <h2 className="card-title" style={{ marginBottom: 20 }}>
              Details
            </h2>
            <div
              style={{
                display: 'grid',
                gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))',
                gap: 32,
              }}
            >
              <div>
                <h3 style={{ fontSize: 14, fontWeight: 700, marginBottom: 16, color: 'var(--text-muted)' }}>
                  Company
                </h3>
                <dl style={{ margin: 0, fontSize: 14, lineHeight: 1.7 }}>
                  <dt style={{ color: 'var(--text-muted)', fontWeight: 500 }}>Company</dt>
                  <dd style={{ margin: '0 0 12px' }}>{row.company_name ?? '—'}</dd>
                  <dt style={{ color: 'var(--text-muted)', fontWeight: 500 }}>Domain</dt>
                  <dd style={{ margin: '0 0 12px' }}>{row.company_domain ?? '—'}</dd>
                  <dt style={{ color: 'var(--text-muted)', fontWeight: 500 }}>Recipient</dt>
                  <dd style={{ margin: '0 0 12px' }}>{row.receipient_name ?? '—'}</dd>
                  <dt style={{ color: 'var(--text-muted)', fontWeight: 500 }}>Email</dt>
                  <dd style={{ margin: '0 0 12px' }}>{row.company_email ?? '—'}</dd>
                  <dt style={{ color: 'var(--text-muted)', fontWeight: 500 }}>Address</dt>
                  <dd style={{ margin: '0 0 12px' }}>
                    {[row.company_add_1, row.company_add_2].filter(Boolean).join(' ') || '—'}
                  </dd>
                  <dt style={{ color: 'var(--text-muted)', fontWeight: 500 }}>City / State / ZIP</dt>
                  <dd style={{ margin: '0 0 12px' }}>
                    {[row.company_city, row.company_state, row.company_zip].filter(Boolean).join(', ') || '—'}
                  </dd>
                  <dt style={{ color: 'var(--text-muted)', fontWeight: 500 }}>Phone</dt>
                  <dd style={{ margin: '0 0 12px' }}>{row.company_phone ?? '—'}</dd>
                  <dt style={{ color: 'var(--text-muted)', fontWeight: 500 }}>Created</dt>
                  <dd style={{ margin: 0 }}>{formatCreatedAt(row.created_at)}</dd>
                </dl>
              </div>
              <div>
                <h3 style={{ fontSize: 14, fontWeight: 700, marginBottom: 16, color: 'var(--text-muted)' }}>
                  Primary user
                </h3>
                {row.user ? (
                  <dl style={{ margin: 0, fontSize: 14, lineHeight: 1.7 }}>
                    <dt style={{ color: 'var(--text-muted)', fontWeight: 500 }}>Name</dt>
                    <dd style={{ margin: '0 0 12px' }}>{row.user.name ?? '—'}</dd>
                    <dt style={{ color: 'var(--text-muted)', fontWeight: 500 }}>Email</dt>
                    <dd style={{ margin: '0 0 12px' }}>{row.user.email ?? '—'}</dd>
                    <dt style={{ color: 'var(--text-muted)', fontWeight: 500 }}>Phone</dt>
                    <dd style={{ margin: 0 }}>{row.user.phone ?? '—'}</dd>
                  </dl>
                ) : (
                  <p style={{ color: 'var(--text-muted)', margin: 0 }}>No linked user record.</p>
                )}
              </div>
            </div>

            <div style={{ marginTop: 28, paddingTop: 20, borderTop: '1px solid var(--border-color)' }}>
              <Link to="/companies" className="btn btn-outline btn-sm">
                Close
              </Link>
            </div>
          </div>
        </>
      ) : null}
    </div>
  );
}
