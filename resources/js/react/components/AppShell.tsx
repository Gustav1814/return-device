import { NavLink, Outlet, useLocation, useNavigate } from 'react-router-dom';
import { createElement, useEffect, useMemo, useState } from 'react';
import { SaasMeProvider, type SaasMeProfile } from '../context/SaasMeContext';
import { useSaasSettings, type SaasSettings } from '../hooks/useSaasSettings';
import { userInitial } from '../utils/userInitial';
import { saasAxios } from '../api/saasAxios';
import {
  LayoutGrid,
  RefreshCw,
  CheckCircle,
  Users,
  Building2,
  Ticket,
  Banknote,
  DollarSign,
  Settings,
  Code,
  Moon,
  Sun,
  LogOut,
  Menu,
  Search,
  Upload,
} from 'lucide-react';

/** Matches `frontend saas/assets/js/app.config.js` paths → React routes (no "New Order" in sidebar — only header CTA). */
const nav = [
  { to: '/dashboard', label: 'Dashboard', icon: LayoutGrid },
  {
    to: '/orders/in-progress',
    label: 'In Progress Orders',
    icon: RefreshCw,
    badge: undefined as number | undefined,
    group: 'Orders' as const,
  },
  { to: '/orders/completed', label: 'Completed Orders', icon: CheckCircle, group: 'Orders' as const },
  { to: '/orders/bulk', label: 'Bulk order', icon: Upload, group: 'Orders' as const },
  { to: '/users', label: 'Users', icon: Users },
  { to: '/companies', label: 'Companies', icon: Building2 },
  { to: '/coupons', label: 'Coupon', icon: Ticket },
  { to: '/commissions', label: 'Commission', icon: Banknote },
  { to: '/settings/prices', label: 'Price Settings', icon: DollarSign },
  { to: '/settings/theme', label: 'Settings', icon: Settings, group: 'System' },
  { to: '/api-docs', label: 'API Integration', icon: Code, group: 'System' },
];

/** Same as `header.blade.php`: Companies, Coupon, Commission only for RR company users. */
const RR_ONLY_PATHS = new Set(['/companies', '/coupons', '/commissions']);

function isNavItemVisible(item: (typeof nav)[0], profile: SaasMeProfile | null): boolean {
  if (!RR_ONLY_PATHS.has(item.to)) return true;
  return profile?.is_rr_company === true;
}

/** Top bar label: turn stored SHOUTCASE into “Title Case” without changing mixed-case names. */
function formatBrandForTopBar(name: string): string {
  const t = name.trim();
  if (!t) return t;
  if (t !== t.toUpperCase()) return t;
  return t.replace(/[A-Za-zÀ-ÿ]+/g, (w) => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase());
}

/** Prefer DB `logo` (always correct path); avoid broken `asset()` URLs when APP_URL ≠ browser origin. */
function resolveBrandLogoSrc(settings: SaasSettings | null): string {
  if (!settings) return '';
  const fname = typeof settings.logo === 'string' ? settings.logo.trim() : '';
  if (fname) {
    return `/storage/logoImage/${fname}`;
  }
  const raw = settings.settings_data?.logoUrl;
  if (typeof raw !== 'string' || !raw.trim()) return '';
  const t = raw.trim();
  if (t.startsWith('/') && !t.startsWith('//')) return t;
  try {
    const u = new URL(t);
    if (u.origin === window.location.origin) {
      return `${u.pathname}${u.search || ''}`;
    }
    return t;
  } catch {
    return t;
  }
}

function SidebarBrandMark({ brandName, logoSrc }: { brandName: string; logoSrc: string }) {
  const [failed, setFailed] = useState(false);
  useEffect(() => {
    setFailed(false);
  }, [logoSrc]);

  const showImg = Boolean(logoSrc) && !failed;

  return (
    <div
      className={`brand-mark${showImg ? '' : ' brand-mark--fallback'}`}
      aria-label={brandName}
    >
      {showImg ? (
        <img
          src={logoSrc}
          alt=""
          className="brand-logo"
          decoding="async"
          onError={() => setFailed(true)}
        />
      ) : (
        <RefreshCw size={20} className="brand-mark-fallback-icon" aria-hidden />
      )}
    </div>
  );
}

export function AppShell() {
  const { theme, setTheme, settings } = useSaasSettings();
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [collapsed, setCollapsed] = useState(false);
  const [me, setMe] = useState<SaasMeProfile | null>(null);
  const navigate = useNavigate();
  const { pathname } = useLocation();

  /** Some static pages use a slim top bar (menu + title only), e.g. `frontend saas/pages/order-create.html`. */
  const compactTop = useMemo(() => {
    if (pathname === '/orders/new') return { title: 'New Return Order' };
    if (pathname === '/orders/bulk') return { title: 'Bulk CSV import' };
    if (pathname === '/users') return { title: 'User Management' };
    if (pathname === '/settings/theme') return { title: 'Settings' };
    if (pathname === '/settings/prices') return { title: 'System Settings' };
    const m = pathname.match(/^\/orders\/(\d+)$/);
    if (m) return { title: 'Order Details' };
    if (/^\/companies\/\d+$/.test(pathname)) return { title: 'Company detail' };
    return null;
  }, [pathname]);

  /** `frontend saas/pages/order-payment.html` has no top bar — only centered summary card. */
  const hideTopBar = /^\/orders\/\d+\/payment$/.test(pathname);

  useEffect(() => {
    saasAxios
      .get('/api/saas/me')
      .then((r) => setMe(r.data))
      .catch(() => setMe(null));
  }, []);

  useEffect(() => {
    if (window.innerWidth <= 768) return;
    const saved = localStorage.getItem('sidebar-collapsed');
    if (saved == null) {
      localStorage.setItem('sidebar-collapsed', 'true');
      setCollapsed(true);
      return;
    }
    setCollapsed(saved === 'true');
  }, []);

  const visibleNav = useMemo(() => nav.filter((i) => isNavItemVisible(i, me)), [me]);

  const grouped = useMemo(() => {
    const dashboard = visibleNav.find((i) => i.to === '/dashboard');
    const orders = visibleNav.filter((i) => i.group === 'Orders');
    const base = visibleNav.filter((i) => !i.group && i.to !== '/dashboard');
    const system = visibleNav.filter((i) => i.group === 'System');
    return { dashboard, orders, base, system };
  }, [visibleNav]);

  const displayName = me?.name?.trim() || 'Harper Nelson';
  const displayEmail = me?.email?.trim() || '';
  const roleLabel = 'Admin Manager';

  const brandName =
    (typeof settings?.settings_data?.brandName === 'string' && settings.settings_data.brandName.trim()) ||
    (typeof (settings as any)?.company?.company_name === 'string' && (settings as any).company.company_name.trim()) ||
    'DeviceReturn';
  const brandNameTopBar = useMemo(() => formatBrandForTopBar(brandName), [brandName]);
  const logoSrc = resolveBrandLogoSrc(settings);

  async function logout() {
    try {
      await saasAxios.post('/logout');
    } catch {
      /* still leave SPA */
    }
    navigate('/login', { replace: true });
  }

  return (
    <>
      <div className="bg-blobs">
        <div className="blob blob-1" />
        <div className="blob blob-2" />
        <div className="blob blob-3" />
      </div>

      <SaasMeProvider value={me}>
      <div className="app-shell">
        {sidebarOpen ? <div className="sidebar-backdrop" onClick={() => setSidebarOpen(false)} /> : null}
        <aside
          className={[
            'sidebar',
            sidebarOpen ? 'mobile-open' : '',
            collapsed ? 'collapsed' : '',
          ]
            .filter(Boolean)
            .join(' ')}
          id="sidebar"
        >
          <div className="sidebar-header">
            <SidebarBrandMark brandName={brandName} logoSrc={logoSrc} />
          </div>

          <nav className="sidebar-nav" id="main-nav">
            {grouped.dashboard ? (
              <NavLink
                key={grouped.dashboard.to}
                to={grouped.dashboard.to}
                className={({ isActive }) =>
                  ['nav-item', isActive ? 'active' : ''].filter(Boolean).join(' ')
                }
                onClick={() => setSidebarOpen(false)}
                data-tooltip={grouped.dashboard.label}
              >
                {createElement(grouped.dashboard.icon, { size: 20 })}
                <span className="nav-label-text">{grouped.dashboard.label}</span>
              </NavLink>
            ) : null}

            {grouped.orders.length ? (
              <div className="nav-group">
                <div className="nav-label">Orders</div>
                {grouped.orders.map((i) => {
                  const Icon = i.icon;
                  return (
                    <NavLink
                      key={i.to}
                      to={i.to}
                      className={({ isActive }) =>
                        ['nav-item', isActive ? 'active' : ''].filter(Boolean).join(' ')
                      }
                      onClick={() => setSidebarOpen(false)}
                      data-tooltip={i.label}
                    >
                      <Icon size={20} />
                      <span className="nav-label-text">{i.label}</span>
                      {typeof (i as any).badge === 'number' && (i as any).badge > 0 ? (
                        <span className="nav-badge" aria-label={`${(i as any).badge} notifications`}>
                          {(i as any).badge > 99 ? '99+' : String((i as any).badge)}
                        </span>
                      ) : null}
                    </NavLink>
                  );
                })}
              </div>
            ) : null}

            {grouped.base.map((i) => {
              const Icon = i.icon;
              return (
                <NavLink
                  key={i.to}
                  to={i.to}
                  className={({ isActive }) =>
                    ['nav-item', isActive ? 'active' : ''].filter(Boolean).join(' ')
                  }
                  onClick={() => setSidebarOpen(false)}
                  data-tooltip={i.label}
                >
                  <Icon size={20} />
                  <span className="nav-label-text">{i.label}</span>
                </NavLink>
              );
            })}

            {grouped.system.length ? (
              <div className="nav-group">
                <div className="nav-label">System</div>
                {grouped.system.map((i) => {
                  const Icon = i.icon;
                  return (
                    <NavLink
                      key={i.to}
                      to={i.to}
                      className={({ isActive }) =>
                        ['nav-item', isActive ? 'active' : ''].filter(Boolean).join(' ')
                      }
                      onClick={() => setSidebarOpen(false)}
                      data-tooltip={i.label}
                    >
                      <Icon size={20} />
                      <span className="nav-label-text">{i.label}</span>
                    </NavLink>
                  );
                })}
              </div>
            ) : null}

            <div className="nav-sep" role="separator" aria-hidden="true" />

            <div
              className="nav-item theme-row"
              role="button"
              tabIndex={0}
              onClick={(e) => {
                if ((e.target as HTMLElement).closest('label.switch')) return;
                setTheme(theme === 'dark' ? 'light' : 'dark');
              }}
              onKeyDown={(e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                  e.preventDefault();
                  setTheme(theme === 'dark' ? 'light' : 'dark');
                }
              }}
              aria-label={theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'}
              data-tooltip={theme === 'dark' ? 'Dark Mode' : 'Light Mode'}
            >
              {theme === 'dark' ? (
                <Moon size={18} className="theme-icon theme-icon--moon" />
              ) : (
                <Sun size={18} className="theme-icon theme-icon--sun" />
              )}
              <span className="nav-label-text">{theme === 'dark' ? 'Dark Mode' : 'Light Mode'}</span>
              <label className="switch" style={{ marginLeft: 'auto' }} onClick={(e) => e.stopPropagation()}>
                <input
                  type="checkbox"
                  id="theme-toggle"
                  checked={theme === 'dark'}
                  onChange={(e) => setTheme(e.target.checked ? 'dark' : 'light')}
                />
                <span className="slider round"></span>
              </label>
            </div>

            <div className="nav-item nav-user" data-tooltip="Profile">
              <span className="user-initial-avatar user-initial-avatar--sidebar" aria-hidden>
                {userInitial(displayName, displayEmail)}
              </span>
              <div className="user-info">
                <span className="user-name">{displayName}</span>
                <span className="user-role">{roleLabel}</span>
              </div>
            </div>

            <button
              type="button"
              className="nav-item nav-logout"
              onClick={logout}
              data-tooltip="Logout"
            >
              <LogOut size={18} />
              <span className="nav-label-text">Logout</span>
            </button>
          </nav>
        </aside>

        <main className="main-container">
          {!hideTopBar ? (
            <header className="top-bar">
              <button
                type="button"
                className="icon-btn"
                id="sidebar-toggle"
                onClick={() => {
                  if (window.innerWidth <= 768) setSidebarOpen((v) => !v);
                  else {
                    setCollapsed((v) => {
                      const next = !v;
                      localStorage.setItem('sidebar-collapsed', String(next));
                      return next;
                    });
                  }
                }}
              >
                <Menu size={18} />
              </button>
              {compactTop ? (
                <>
                  <div className="top-bar-spacer" aria-hidden />
                  <div className="top-bar-end">
                    <span className="top-bar-brand-name top-bar-brand-name--toolbar">{brandNameTopBar}</span>
                    <div className="top-bar-page-chip">
                      <span className="top-bar-page-chip__shine" aria-hidden />
                      <span className="top-bar-page-chip__text">{compactTop.title}</span>
                    </div>
                  </div>
                </>
              ) : (
                <>
                  <div className="search-container">
                    <Search className="search-icon" size={18} />
                    <input
                      type="text"
                      className="search-input"
                      placeholder="Search orders, serials... (⌘ + F)"
                    />
                  </div>
                  <div className="top-actions">
                    <span className="top-bar-brand-name top-bar-brand-name--toolbar">{brandNameTopBar}</span>
                    <NavLink
                      to="/order"
                      className={({ isActive }) =>
                        [
                          'top-bar-page-chip',
                          'top-bar-page-chip--link',
                          isActive ? 'top-bar-page-chip--current' : '',
                        ]
                          .filter(Boolean)
                          .join(' ')
                      }
                    >
                      <span className="top-bar-page-chip__shine" aria-hidden />
                      <span className="top-bar-page-chip__text">New Order</span>
                    </NavLink>
                  </div>
                </>
              )}
            </header>
          ) : null}

          <Outlet />
        </main>
      </div>
      </SaasMeProvider>
    </>
  );
}
