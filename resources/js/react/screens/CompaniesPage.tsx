import { useCallback, useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { Filter, MoreHorizontal, X } from 'lucide-react';
import { saasAxios } from '../api/saasAxios';
import { US_STATES } from '../data/usStates';
import { useToast } from '../context/ToastContext';

type CompanyListRow = {
  id: number;
  company_name?: string | null;
  company_domain?: string | null;
  domain?: string | null;
  active_orders?: number;
  status_label?: string;
  created_at?: string | null;
};

type CompanyEditPayload = {
  id: number;
  company_name: string;
  domain: string;
  company_domain: string;
  receipient_name: string;
  company_email: string;
  company_add_1: string;
  company_add_2: string;
  company_city: string;
  company_state: string;
  company_zip: string;
  company_phone: string;
  user_status: 'active' | 'inactive';
};

function emptyEditForm(): Omit<CompanyEditPayload, 'id'> {
  return {
    company_name: '',
    domain: '',
    company_domain: '',
    receipient_name: '',
    company_email: '',
    company_add_1: '',
    company_add_2: '',
    company_city: '',
    company_state: '',
    company_zip: '',
    company_phone: '',
    user_status: 'active',
  };
}

/** US phone display helper (matches classic Blade behavior). */
function formatUsPhoneDigits(raw: string): string {
  const d = raw.replace(/\D/g, '').slice(0, 10);
  const x = d.match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
  if (!x) return '';
  if (!x[2]) return x[1];
  return `(${x[1]})${x[2]}${x[3] ? `-${x[3]}` : ''}`;
}

export function CompaniesPage() {
  const { showToast } = useToast();
  const [data, setData] = useState<{ data?: CompanyListRow[] } | null>(null);
  const [loading, setLoading] = useState(true);
  const [filterOpen, setFilterOpen] = useState(false);
  const [search, setSearch] = useState('');
  const [menuId, setMenuId] = useState<number | null>(null);

  const [editOpen, setEditOpen] = useState(false);
  const [editId, setEditId] = useState<number | null>(null);
  const [editLoading, setEditLoading] = useState(false);
  const [editSaving, setEditSaving] = useState(false);
  const [editForm, setEditForm] = useState<Omit<CompanyEditPayload, 'id'>>(() => emptyEditForm());
  const [editError, setEditError] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});

  const reloadCompanies = useCallback(() => {
    setLoading(true);
    saasAxios
      .get('/api/saas/companies')
      .then((r) => setData(r.data))
      .catch(() => setData({ data: [] }))
      .finally(() => setLoading(false));
  }, []);

  useEffect(() => {
    reloadCompanies();
  }, [reloadCompanies]);

  useEffect(() => {
    if (menuId == null) return;
    const close = () => setMenuId(null);
    document.addEventListener('click', close);
    return () => document.removeEventListener('click', close);
  }, [menuId]);

  useEffect(() => {
    if (!editOpen) return;
    const onKey = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        setEditOpen(false);
        setEditId(null);
        setEditError(null);
        setFieldErrors({});
      }
    };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [editOpen]);

  const filtered = useMemo(() => {
    const list = data?.data ?? [];
    const q = search.trim().toLowerCase();
    if (!q) return list;
    return list.filter((c) => {
      const name = String(c.company_name ?? '').toLowerCase();
      const dom = String(c.company_domain ?? c.domain ?? '').toLowerCase();
      return name.includes(q) || dom.includes(q);
    });
  }, [data, search]);

  async function openEditCompany(id: number) {
    setMenuId(null);
    setEditId(id);
    setEditOpen(true);
    setEditLoading(true);
    setEditError(null);
    setFieldErrors({});
    setEditForm(emptyEditForm());
    try {
      const { data: row } = await saasAxios.get<CompanyEditPayload>(`/api/saas/companies/${id}`);
      setEditForm({
        company_name: String(row.company_name ?? ''),
        domain: String(row.domain ?? ''),
        company_domain: String(row.company_domain ?? ''),
        receipient_name: String(row.receipient_name ?? ''),
        company_email: String(row.company_email ?? ''),
        company_add_1: String(row.company_add_1 ?? ''),
        company_add_2: String(row.company_add_2 ?? ''),
        company_city: String(row.company_city ?? ''),
        company_state: String(row.company_state ?? ''),
        company_zip: String(row.company_zip ?? ''),
        company_phone: formatUsPhoneDigits(String(row.company_phone ?? '')),
        user_status: row.user_status === 'inactive' ? 'inactive' : 'active',
      });
    } catch (err: unknown) {
      const ax = err as { response?: { data?: { message?: string } }; message?: string };
      setEditError(ax.response?.data?.message ?? ax.message ?? 'Could not load company.');
      showToast('Could not load company for editing.', 'default');
    } finally {
      setEditLoading(false);
    }
  }

  function closeEditModal() {
    if (editSaving) return;
    setEditOpen(false);
    setEditId(null);
    setEditError(null);
    setFieldErrors({});
  }

  async function saveEdit() {
    if (editId == null) return;
    setEditSaving(true);
    setEditError(null);
    setFieldErrors({});
    const body = {
      company_name: editForm.company_name.trim(),
      domain: editForm.domain.trim(),
      company_domain: editForm.company_domain.trim(),
      receipient_name: editForm.receipient_name.trim(),
      company_email: editForm.company_email.trim(),
      company_add_1: editForm.company_add_1.trim(),
      company_add_2: editForm.company_add_2.trim(),
      company_phone: editForm.company_phone.trim(),
      company_city: editForm.company_city.trim(),
      company_state: editForm.company_state,
      company_zip: editForm.company_zip.trim(),
      user_status: editForm.user_status,
    };
    try {
      await saasAxios.put(`/api/saas/companies/${editId}`, body);
      showToast('Company updated.', 'success');
      closeEditModal();
      reloadCompanies();
    } catch (err: unknown) {
      const ax = err as {
        response?: { status?: number; data?: { message?: string; errors?: Record<string, string[]> } };
      };
      const st = ax.response?.status;
      const d = ax.response?.data;
      if (st === 422 && d?.errors) {
        setFieldErrors(d.errors);
        setEditError('Please fix the highlighted fields.');
      } else {
        setEditError(d?.message ?? 'Could not save company.');
        showToast(d?.message ?? 'Could not save company.', 'default');
      }
    } finally {
      setEditSaving(false);
    }
  }

  function fieldErr(name: string): string | undefined {
    const a = fieldErrors[name];
    return Array.isArray(a) && a.length ? a[0] : undefined;
  }

  return (
    <div className="content-wrapper">
      <div className="page-header reveal active">
        <h1 className="page-title">Companies</h1>
        <div className="toolbar">
          <div className="filter-wrap">
            <button type="button" className="btn btn-outline" onClick={() => setFilterOpen((v) => !v)}>
              <Filter size={18} /> Filter
            </button>
            {filterOpen ? (
              <div className="filter-popover card" role="dialog" aria-label="Filter companies">
                <div className="filter-row">
                  <label className="filter-label" htmlFor="company-search">
                    Search name or domain
                  </label>
                  <input
                    id="company-search"
                    type="search"
                    className="form-input"
                    placeholder="Type to filter…"
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    autoFocus
                  />
                </div>
                <div className="filter-actions">
                  <button
                    type="button"
                    className="btn btn-outline btn-sm"
                    onClick={() => {
                      setSearch('');
                      setFilterOpen(false);
                    }}
                  >
                    Clear & close
                  </button>
                </div>
              </div>
            ) : null}
          </div>
        </div>
      </div>

      <div className="card reveal active">
        <div className="table-responsive">
          <table className="data-table">
            <thead>
              <tr>
                <th>Company Name</th>
                <th>Domain</th>
                <th>Active Orders</th>
                <th>Status</th>
                <th>Joined</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {filtered.map((c) => (
                <tr key={c.id}>
                  <td>{c.company_name ?? '—'}</td>
                  <td>{c.company_domain ?? c.domain ?? '—'}</td>
                  <td>{c.active_orders ?? 0}</td>
                  <td>
                    <span className="status-pill status-completed">{c.status_label ?? 'Active'}</span>
                  </td>
                  <td>{c.created_at ? String(c.created_at).slice(0, 10) : '—'}</td>
                  <td className="table-actions-cell">
                    <button
                      type="button"
                      className="icon-btn"
                      aria-label="More actions"
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
                        <Link
                          to={`/companies/${c.id}`}
                          role="menuitem"
                          className="table-actions-menu__link"
                          onClick={() => setMenuId(null)}
                        >
                          View details
                        </Link>
                        <button type="button" role="menuitem" onClick={() => void openEditCompany(c.id)}>
                          Edit company
                        </button>
                      </div>
                    ) : null}
                  </td>
                </tr>
              ))}
              {!loading && Array.isArray(data?.data) && filtered.length === 0 ? (
                <tr>
                  <td colSpan={6}>{search.trim() ? 'No companies match your search.' : 'No companies.'}</td>
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

      {editOpen ? (
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
            if (e.target === e.currentTarget) closeEditModal();
          }}
        >
          <div
            className="card reveal active"
            role="dialog"
            aria-modal="true"
            aria-labelledby="company-edit-title"
            style={{
              width: '100%',
              maxWidth: 720,
              maxHeight: 'min(90vh, 900px)',
              overflow: 'auto',
              margin: 0,
              position: 'relative',
              boxShadow: 'var(--shadow-card, 0 24px 48px rgba(15, 23, 42, 0.18))',
            }}
            onMouseDown={(e) => e.stopPropagation()}
          >
            <div
              style={{
                display: 'flex',
                alignItems: 'flex-start',
                justifyContent: 'space-between',
                gap: 16,
                padding: '24px 28px 0',
                borderBottom: '1px solid var(--border-color)',
                paddingBottom: 20,
              }}
            >
              <div>
                <h2 id="company-edit-title" className="page-title" style={{ fontSize: '1.25rem', margin: 0 }}>
                  Edit company
                </h2>
                <p style={{ margin: '8px 0 0', fontSize: 14, color: 'var(--text-muted)' }}>
                  Same fields as the classic admin form—saved to your tenant companies.
                </p>
              </div>
              <button
                type="button"
                className="icon-btn"
                aria-label="Close"
                disabled={editSaving}
                onClick={closeEditModal}
              >
                <X size={20} />
              </button>
            </div>

            <div style={{ padding: '24px 28px 28px' }}>
              {editLoading ? (
                <p style={{ color: 'var(--text-muted)', margin: 0 }}>Loading company…</p>
              ) : (
                <>
                  {editError && !Object.keys(fieldErrors).length ? (
                    <div className="form-group" style={{ color: 'crimson', fontSize: 14, marginBottom: 16 }}>
                      {editError}
                    </div>
                  ) : null}
                  {editError && Object.keys(fieldErrors).length ? (
                    <div className="form-group" style={{ color: 'crimson', fontSize: 14, marginBottom: 16 }}>
                      {editError}
                    </div>
                  ) : null}

                  <div
                    style={{
                      display: 'grid',
                      gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))',
                      gap: '16px 20px',
                    }}
                  >
                    <div className="form-group" style={{ marginBottom: 0 }}>
                      <label className="form-label" htmlFor="ce-name">
                        Company name
                      </label>
                      <input
                        id="ce-name"
                        className="form-input"
                        value={editForm.company_name}
                        maxLength={75}
                        onChange={(e) => setEditForm((f) => ({ ...f, company_name: e.target.value }))}
                      />
                      {fieldErr('company_name') ? (
                        <span style={{ fontSize: 12, color: 'crimson' }}>{fieldErr('company_name')}</span>
                      ) : null}
                    </div>
                    <div className="form-group" style={{ marginBottom: 0 }}>
                      <label className="form-label" htmlFor="ce-domain">
                        Domain
                      </label>
                      <input
                        id="ce-domain"
                        className="form-input"
                        value={editForm.domain}
                        maxLength={75}
                        onChange={(e) => setEditForm((f) => ({ ...f, domain: e.target.value }))}
                      />
                      {fieldErr('domain') ? (
                        <span style={{ fontSize: 12, color: 'crimson' }}>{fieldErr('domain')}</span>
                      ) : null}
                    </div>
                    <div className="form-group" style={{ marginBottom: 0 }}>
                      <label className="form-label" htmlFor="ce-company-domain">
                        Company domain (read-only)
                      </label>
                      <input
                        id="ce-company-domain"
                        className="form-input"
                        value={editForm.company_domain}
                        maxLength={75}
                        readOnly
                        style={{ opacity: 0.85 }}
                      />
                      {fieldErr('company_domain') ? (
                        <span style={{ fontSize: 12, color: 'crimson' }}>{fieldErr('company_domain')}</span>
                      ) : null}
                    </div>
                    <div className="form-group" style={{ marginBottom: 0 }}>
                      <label className="form-label" htmlFor="ce-recipient">
                        Recipient name
                      </label>
                      <input
                        id="ce-recipient"
                        className="form-input"
                        value={editForm.receipient_name}
                        maxLength={75}
                        onChange={(e) => setEditForm((f) => ({ ...f, receipient_name: e.target.value }))}
                      />
                      {fieldErr('receipient_name') ? (
                        <span style={{ fontSize: 12, color: 'crimson' }}>{fieldErr('receipient_name')}</span>
                      ) : null}
                    </div>
                    <div className="form-group" style={{ marginBottom: 0 }}>
                      <label className="form-label" htmlFor="ce-email">
                        Company email
                      </label>
                      <input
                        id="ce-email"
                        type="email"
                        className="form-input"
                        value={editForm.company_email}
                        maxLength={75}
                        onChange={(e) => setEditForm((f) => ({ ...f, company_email: e.target.value }))}
                      />
                      {fieldErr('company_email') ? (
                        <span style={{ fontSize: 12, color: 'crimson' }}>{fieldErr('company_email')}</span>
                      ) : null}
                    </div>
                    <div className="form-group" style={{ marginBottom: 0, gridColumn: '1 / -1' }}>
                      <label className="form-label" htmlFor="ce-add1">
                        Address line 1
                      </label>
                      <input
                        id="ce-add1"
                        className="form-input"
                        value={editForm.company_add_1}
                        maxLength={75}
                        onChange={(e) => setEditForm((f) => ({ ...f, company_add_1: e.target.value }))}
                      />
                      {fieldErr('company_add_1') ? (
                        <span style={{ fontSize: 12, color: 'crimson' }}>{fieldErr('company_add_1')}</span>
                      ) : null}
                    </div>
                    <div className="form-group" style={{ marginBottom: 0, gridColumn: '1 / -1' }}>
                      <label className="form-label" htmlFor="ce-add2">
                        Address line 2
                      </label>
                      <input
                        id="ce-add2"
                        className="form-input"
                        value={editForm.company_add_2}
                        maxLength={75}
                        onChange={(e) => setEditForm((f) => ({ ...f, company_add_2: e.target.value }))}
                      />
                      {fieldErr('company_add_2') ? (
                        <span style={{ fontSize: 12, color: 'crimson' }}>{fieldErr('company_add_2')}</span>
                      ) : null}
                    </div>
                    <div className="form-group" style={{ marginBottom: 0 }}>
                      <label className="form-label" htmlFor="ce-city">
                        City
                      </label>
                      <input
                        id="ce-city"
                        className="form-input"
                        value={editForm.company_city}
                        maxLength={45}
                        onChange={(e) => setEditForm((f) => ({ ...f, company_city: e.target.value }))}
                      />
                      {fieldErr('company_city') ? (
                        <span style={{ fontSize: 12, color: 'crimson' }}>{fieldErr('company_city')}</span>
                      ) : null}
                    </div>
                    <div className="form-group" style={{ marginBottom: 0 }}>
                      <label className="form-label" htmlFor="ce-state">
                        State
                      </label>
                      <select
                        id="ce-state"
                        className="form-input"
                        value={editForm.company_state}
                        onChange={(e) => setEditForm((f) => ({ ...f, company_state: e.target.value }))}
                      >
                        <option value="">State / Province</option>
                        {US_STATES.map((s) => (
                          <option key={s.code} value={s.code}>
                            {s.name}
                          </option>
                        ))}
                      </select>
                      {fieldErr('company_state') ? (
                        <span style={{ fontSize: 12, color: 'crimson' }}>{fieldErr('company_state')}</span>
                      ) : null}
                    </div>
                    <div className="form-group" style={{ marginBottom: 0 }}>
                      <label className="form-label" htmlFor="ce-zip">
                        ZIP
                      </label>
                      <input
                        id="ce-zip"
                        className="form-input"
                        value={editForm.company_zip}
                        maxLength={75}
                        onChange={(e) => setEditForm((f) => ({ ...f, company_zip: e.target.value }))}
                      />
                      {fieldErr('company_zip') ? (
                        <span style={{ fontSize: 12, color: 'crimson' }}>{fieldErr('company_zip')}</span>
                      ) : null}
                    </div>
                    <div className="form-group" style={{ marginBottom: 0 }}>
                      <label className="form-label" htmlFor="ce-phone">
                        Phone
                      </label>
                      <input
                        id="ce-phone"
                        type="tel"
                        className="form-input"
                        value={editForm.company_phone}
                        onChange={(e) =>
                          setEditForm((f) => ({ ...f, company_phone: formatUsPhoneDigits(e.target.value) }))
                        }
                        placeholder="(555)555-5555"
                      />
                      {fieldErr('company_phone') ? (
                        <span style={{ fontSize: 12, color: 'crimson' }}>{fieldErr('company_phone')}</span>
                      ) : null}
                    </div>
                    <div className="form-group" style={{ marginBottom: 0 }}>
                      <label className="form-label" htmlFor="ce-user-status">
                        User account status
                      </label>
                      <select
                        id="ce-user-status"
                        className="form-input"
                        value={editForm.user_status}
                        onChange={(e) =>
                          setEditForm((f) => ({
                            ...f,
                            user_status: e.target.value === 'inactive' ? 'inactive' : 'active',
                          }))
                        }
                      >
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                      </select>
                      {fieldErr('user_status') ? (
                        <span style={{ fontSize: 12, color: 'crimson' }}>{fieldErr('user_status')}</span>
                      ) : null}
                    </div>
                  </div>

                  <div
                    style={{
                      display: 'flex',
                      justifyContent: 'flex-end',
                      gap: 12,
                      marginTop: 28,
                      paddingTop: 20,
                      borderTop: '1px solid var(--border-color)',
                    }}
                  >
                    <button type="button" className="btn btn-outline" disabled={editSaving} onClick={closeEditModal}>
                      Cancel
                    </button>
                    <button type="button" className="btn btn-primary" disabled={editSaving} onClick={() => void saveEdit()}>
                      {editSaving ? 'Saving…' : 'Save changes'}
                    </button>
                  </div>
                </>
              )}
            </div>
          </div>
        </div>
      ) : null}
    </div>
  );
}
