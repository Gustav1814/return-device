import { useEffect, useMemo, useState } from 'react';
import { saasAxios } from '../api/saasAxios';
import { userInitial } from '../utils/userInitial';

type UserRow = {
  id: number;
  name?: string | null;
  email?: string | null;
  company_name?: string | null;
  phone?: string | null;
  role?: string | null;
  status?: string | number | null;
  created_at?: string | null;
  updated_at?: string | null;
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

function formatCreatedAt(iso: string | null | undefined) {
  if (!iso) return '—';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return String(iso).slice(0, 10);
  return d.toLocaleDateString(undefined, { month: 'short', day: '2-digit', year: 'numeric' });
}

function formatDateYmd(iso: string | null | undefined) {
  if (!iso) return '—';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return String(iso).slice(0, 10);
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
}

function formatPhone(p: string | null | undefined) {
  const raw = String(p ?? '').trim();
  if (!raw) return '—';
  const d = raw.replace(/\D/g, '');
  if (d.length === 10) {
    return `(${d.slice(0, 3)})${d.slice(3, 6)}-${d.slice(6)}`;
  }
  return raw;
}

function formatRoleLabel(role: string | null | undefined) {
  const r = String(role ?? '').trim();
  if (!r) return '—';
  if (r.includes(' ') || r.includes('-')) {
    return r
      .split(/[\s_-]+/)
      .map((w) => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase())
      .join(' ');
  }
  return r.charAt(0).toUpperCase() + r.slice(1).toLowerCase();
}

function isUserActive(u: UserRow) {
  const s = u.status;
  if (s === 1 || s === '1') return true;
  return String(s ?? '').toLowerCase() === 'active';
}

export function UsersPage() {
  const [page, setPage] = useState(1);
  const [searchDraft, setSearchDraft] = useState('');
  const [appliedSearch, setAppliedSearch] = useState('');
  const [resp, setResp] = useState<Paginated<UserRow> | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    setLoading(true);
    saasAxios
      .get('/api/saas/users', {
        params: {
          page,
          ...(appliedSearch.trim() ? { search: appliedSearch.trim() } : {}),
        },
      })
      .then((r) => setResp(r.data))
      .catch(() => setResp({ data: [] }))
      .finally(() => setLoading(false));
  }, [page, appliedSearch]);

  function runSearch() {
    setAppliedSearch(searchDraft.trim());
    setPage(1);
  }

  const rows = resp?.data ?? [];
  const total = resp?.total ?? rows.length;
  const from = resp?.from ?? (rows.length ? (page - 1) * (resp?.per_page ?? 15) + 1 : 0);
  const to = resp?.to ?? (rows.length ? from + rows.length - 1 : 0);

  const summary = useMemo(() => {
    if (!rows.length) return 'Showing 0 users';
    return `Showing ${from} to ${to} of ${total} users`;
  }, [from, to, total, rows.length]);

  return (
    <div className="content-wrapper">
      <div className="page-header reveal active">
        <h1 className="page-title">Users</h1>
      </div>

      <div className="card reveal active">
        <div
          style={{
            padding: '20px 28px',
            borderBottom: '1px solid var(--border-color)',
          }}
        >
          <div
            style={{
              display: 'flex',
              gap: 12,
              flexWrap: 'wrap',
              alignItems: 'center',
            }}
          >
            <input
              type="search"
              className="form-input"
              placeholder="Search by user ID, name, email, or company…"
              aria-label="Search users"
              value={searchDraft}
              onChange={(e) => setSearchDraft(e.target.value)}
              onKeyDown={(e) => {
                if (e.key === 'Enter') runSearch();
              }}
              style={{ maxWidth: 420, flex: '1 1 240px', minWidth: 0 }}
            />
            <button type="button" className="btn btn-outline" onClick={runSearch}>
              Search
            </button>
          </div>
        </div>

        <div className="table-responsive">
          <table className="data-table users-table-wide">
            <thead>
              <tr>
                <th>User ID</th>
                <th>User</th>
                <th>Company</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Last Active</th>
              </tr>
            </thead>
            <tbody>
              {rows.map((u) => {
                const active = isUserActive(u);
                return (
                  <tr key={u.id}>
                    <td>{u.id}</td>
                    <td>
                      <div style={{ display: 'flex', alignItems: 'center', gap: 12, minWidth: 200 }}>
                        <span className="user-initial-avatar" aria-hidden>
                          {userInitial(u.name, u.email)}
                        </span>
                        <div>
                          <div style={{ fontWeight: 600 }}>{u.name ?? '—'}</div>
                          <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>{u.email ?? '—'}</div>
                        </div>
                      </div>
                    </td>
                    <td>{u.company_name ?? '—'}</td>
                    <td>{formatPhone(u.phone)}</td>
                    <td>{formatRoleLabel(u.role)}</td>
                    <td>
                      <span
                        className={[
                          'status-pill',
                          active ? 'status-completed' : 'status-pending',
                        ].join(' ')}
                      >
                        {active ? 'ACTIVE' : 'INACTIVE'}
                      </span>
                    </td>
                    <td>{formatCreatedAt(u.created_at)}</td>
                    <td>{formatDateYmd(u.updated_at)}</td>
                  </tr>
                );
              })}
              {!loading && rows.length === 0 ? (
                <tr>
                  <td colSpan={8} style={{ color: 'var(--text-muted)' }}>
                    No users found.
                  </td>
                </tr>
              ) : null}
              {loading ? (
                <tr>
                  <td colSpan={8} style={{ color: 'var(--text-muted)' }}>
                    Loading…
                  </td>
                </tr>
              ) : null}
            </tbody>
          </table>
        </div>

        <div
          className="pagination"
          style={{
            marginTop: 20,
            padding: '0 28px 28px',
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center',
            flexWrap: 'wrap',
            gap: 12,
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
              disabled={
                loading || !resp || (resp.last_page != null && page >= (resp.last_page ?? 1))
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
