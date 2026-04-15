import { useEffect, useMemo, useRef, useState } from 'react';
import { useLocation } from 'react-router-dom';
import axios from 'axios';
import { Check, ChevronDown } from 'lucide-react';
import { useSaasSettings } from '../hooks/useSaasSettings';
import { saasAxios } from '../api/saasAxios';
import { useToast } from '../context/ToastContext';
import { applyDocumentTheme } from '../theme/applyDocumentTheme';
import {
  accentIsClampedForUiTheme,
  clampAccentForUiTheme,
  isAccentTooLightForDarkMode,
  normalizeHex,
} from '../utils/chartTheme';

type WidgetRow = { id: string; visible: boolean };

const CHART_DEFS: { id: string; label: string }[] = [
  { id: 'shipments_trend', label: 'Sales overview chart' },
  { id: 'status_breakdown', label: 'Equipment distribution chart' },
];

const THEME_OPTIONS = [
  { value: 'light' as const, label: 'Light' },
  { value: 'dark' as const, label: 'Dark' },
  { value: 'system' as const, label: 'System' },
];

function normHex(c: string) {
  const s = (c || '').trim();
  if (!s.startsWith('#')) return `#${s}`;
  return s;
}

export function SettingsThemePage() {
  const { showToast } = useToast();
  const location = useLocation();
  const { settings, reloadSettings } = useSaasSettings();
  const [companyName, setCompanyName] = useState('');
  const [logoUrl, setLogoUrl] = useState('');
  const [primary, setPrimary] = useState('#10b981');
  const [themeSelect, setThemeSelect] = useState<'light' | 'dark' | 'system'>('light');
  const [charts, setCharts] = useState<WidgetRow[]>([]);
  const [saving, setSaving] = useState(false);
  const [themeMenuOpen, setThemeMenuOpen] = useState(false);
  const themeSelectRef = useRef<HTMLDivElement>(null);
  const prevResolvedUiRef = useRef<'light' | 'dark' | null>(null);

  useEffect(() => {
    if (!themeMenuOpen) return;
    const onDocMouseDown = (e: MouseEvent) => {
      const el = themeSelectRef.current;
      if (el && !el.contains(e.target as Node)) setThemeMenuOpen(false);
    };
    const onKey = (e: KeyboardEvent) => {
      if (e.key === 'Escape') setThemeMenuOpen(false);
    };
    document.addEventListener('mousedown', onDocMouseDown);
    document.addEventListener('keydown', onKey);
    return () => {
      document.removeEventListener('mousedown', onDocMouseDown);
      document.removeEventListener('keydown', onKey);
    };
  }, [themeMenuOpen]);

  useEffect(() => {
    const raw = (location.hash || '').replace(/^#/, '');
    if (!raw) return;
    const t = window.setTimeout(() => {
      document.getElementById(raw)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 120);
    return () => window.clearTimeout(t);
  }, [location.hash, location.pathname]);

  useEffect(() => {
    const sd = settings?.settings_data ?? {};
    setCompanyName(
      (typeof sd.brandName === 'string' && sd.brandName) ||
        settings?.company?.company_name ||
        '',
    );
    setLogoUrl(() => {
      const fname =
        typeof settings?.logo === 'string' && settings.logo.trim()
          ? `/storage/logoImage/${settings.logo.trim()}`
          : '';
      if (fname) return fname;
      const u = typeof sd.logoUrl === 'string' ? sd.logoUrl.trim() : '';
      if (!u) return '';
      if (u.startsWith('/') && !u.startsWith('//')) return u;
      try {
        const parsed = new URL(u);
        if (parsed.origin === window.location.origin) {
          return `${parsed.pathname}${parsed.search || ''}`;
        }
      } catch {
        /* keep raw */
      }
      return u;
    });
    const storedUser =
      (typeof sd.primaryColorUser === 'string' && sd.primaryColorUser.trim() && sd.primaryColorUser) ||
      settings?.btn_bg_color ||
      sd.primaryColor ||
      '#10b981';
    setPrimary(normHex(String(storedUser)));
    if (sd.themePreference === 'system') setThemeSelect('system');
    else if (sd.theme === 'dark') setThemeSelect('dark');
    else setThemeSelect('light');
    const ch = Array.isArray(sd.dashboard?.charts) ? sd.dashboard.charts : [];
    const chBase = CHART_DEFS.map((d) => {
      const hit = ch.find((x: WidgetRow) => x.id === d.id);
      return { id: d.id, visible: hit ? !!hit.visible : true };
    });
    setCharts(chBase);
  }, [settings]);

  const resolvedUiMode = useMemo((): 'light' | 'dark' => {
    if (themeSelect === 'system') {
      return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    return themeSelect === 'dark' ? 'dark' : 'light';
  }, [themeSelect]);

  /** Preview / save: readable accent for the current UI mode */
  const effectivePrimary = useMemo(
    () => clampAccentForUiTheme(primary, resolvedUiMode),
    [primary, resolvedUiMode],
  );

  const accentClampHint = useMemo(() => {
    if (!accentIsClampedForUiTheme(primary, resolvedUiMode)) return null;
    return resolvedUiMode === 'dark'
      ? 'This color is adjusted automatically for contrast on dark mode. Your choice is still saved; buttons and charts use the adjusted tone.'
      : 'This color is adjusted automatically for contrast on light mode. Your choice is still saved; buttons and charts use the adjusted tone.';
  }, [primary, resolvedUiMode]);

  useEffect(() => {
    const was = prevResolvedUiRef.current;
    prevResolvedUiRef.current = resolvedUiMode;
    if (was !== 'dark' || resolvedUiMode !== 'light') return;
    setPrimary((p) => {
      const n = normalizeHex(normHex(p));
      if (n && isAccentTooLightForDarkMode(n)) return '#000000';
      return p;
    });
  }, [resolvedUiMode]);

  function formatUploadError(err: unknown): string {
    if (axios.isAxiosError(err)) {
      const d = err.response?.data as
        | { message?: string; errors?: Record<string, string[]> }
        | undefined;
      if (d?.errors?.logo?.[0]) return d.errors.logo[0];
      if (typeof d?.message === 'string' && d.message) return d.message;
      if (err.response?.status === 413) return 'File is too large for the server (try under 2 MB).';
    }
    return 'Could not upload logo.';
  }

  async function uploadLogo(file: File) {
    const fd = new FormData();
    fd.append('logo', file);
    // Let the browser set multipart boundary (do not set Content-Type manually).
    const { data } = await saasAxios.post('/api/saas/settings/logo', fd);
    if (data?.logoUrl) {
      setLogoUrl(String(data.logoUrl));
    } else if (data?.logo) {
      setLogoUrl(`/storage/logoImage/${String(data.logo)}`);
    }
    reloadSettings();
  }

  async function save() {
    setSaving(true);
    try {
      const sdPrev = settings?.settings_data ?? {};
      const resolved =
        themeSelect === 'system'
          ? window.matchMedia('(prefers-color-scheme: dark)').matches
            ? 'dark'
            : 'light'
          : themeSelect;
      const mode = resolved === 'dark' ? 'dark' : 'light';
      const effective = clampAccentForUiTheme(primary, mode);
      const rawPick = normHex(primary);
      const userNorm = normalizeHex(rawPick) ?? rawPick;
      const prevDash = sdPrev.dashboard ?? {};
      const { widgets: _omitWidgets, ...dashRest } = prevDash as Record<string, unknown> & {
        widgets?: unknown;
      };

      await saasAxios.put('/api/saas/settings', {
        btn_bg_color: effective,
        settings_data: {
          ...sdPrev,
          brandName: companyName,
          logoUrl,
          primaryColor: effective,
          primaryColorUser: userNorm,
          theme: resolved === 'dark' ? 'dark' : 'light',
          themePreference: themeSelect,
          dashboard: {
            ...dashRest,
            charts,
          },
        },
      });
      applyDocumentTheme(resolved === 'dark' ? 'dark' : 'light');
      document.documentElement.style.setProperty('--brand-primary', effective);
      reloadSettings();
      showToast('Settings saved.', 'success');
    } catch (err) {
      if (axios.isAxiosError(err)) {
        const d = err.response?.data as { errors?: { btn_bg_color?: string[] }; message?: string } | undefined;
        const line = d?.errors?.btn_bg_color?.[0];
        if (line) {
          showToast(line, 'error');
          return;
        }
        if (typeof d?.message === 'string' && d.message) {
          showToast(d.message, 'error');
          return;
        }
      }
      showToast('Could not save settings.', 'error');
    } finally {
      setSaving(false);
    }
  }

  return (
    <div className="content-wrapper">
      <div className="page-header reveal active">
        <h1 className="page-title">Company Branding</h1>
        <button
          className="top-bar-page-chip top-bar-page-chip--btn"
          type="button"
          id="save-settings"
          onClick={save}
          disabled={saving}
        >
          <span className="top-bar-page-chip__shine" aria-hidden />
          <span className="top-bar-page-chip__text">{saving ? 'Saving…' : 'Save Changes'}</span>
        </button>
      </div>

      <div className="dashboard-row">
        <div className="card reveal active">
          <h3 className="card-title" style={{ marginBottom: 24 }}>
            Visual Identity
          </h3>

          <div className="form-group">
            <label className="form-label">Company Name</label>
            <input
              type="text"
              className="form-input"
              value={companyName}
              onChange={(e) => setCompanyName(e.target.value)}
            />
          </div>

          <div className="form-group">
            <label className="form-label">Logo Preview</label>
            <div
              style={{
                border: '1px solid var(--border-color)',
                borderRadius: 14,
                minHeight: 120,
                padding: 16,
                background: 'var(--surface-elevated, rgba(255,255,255,0.6))',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                marginBottom: 12,
              }}
            >
              {logoUrl.trim() ? (
                <img
                  src={logoUrl}
                  alt="Company logo preview"
                  style={{ maxHeight: 88, maxWidth: '100%', objectFit: 'contain' }}
                  onError={(e) => {
                    (e.currentTarget as HTMLImageElement).style.display = 'none';
                  }}
                />
              ) : (
                <span style={{ color: 'var(--text-muted)' }}>No logo uploaded yet.</span>
              )}
            </div>
            <label className="btn btn-outline" style={{ display: 'inline-flex', alignItems: 'center' }}>
              Upload
              <input
                type="file"
                accept="image/*"
                style={{ display: 'none' }}
                onChange={async (e) => {
                  const f = e.target.files?.[0];
                  if (!f) return;
                  try {
                    await uploadLogo(f);
                    showToast('Logo uploaded.', 'success');
                  } catch (err) {
                    showToast(formatUploadError(err), 'error');
                  } finally {
                    e.currentTarget.value = '';
                  }
                }}
              />
            </label>
          </div>

          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 20 }}>
            <div className="form-group">
              <label className="form-label">Primary Color</label>
              <div style={{ display: 'flex', gap: 8 }}>
                <input
                  type="color"
                  className="form-input"
                  style={{ width: 48, padding: 2 }}
                  value={primary.length >= 7 ? primary.slice(0, 7) : '#10b981'}
                  onChange={(e) => setPrimary(e.target.value)}
                />
                <input
                  type="text"
                  className="form-input"
                  value={primary}
                  onChange={(e) => setPrimary(e.target.value)}
                />
              </div>
              {accentClampHint ? (
                <p className="form-hint" style={{ marginTop: 8, marginBottom: 0, fontSize: 13, opacity: 0.85 }}>
                  {accentClampHint}
                </p>
              ) : null}
            </div>
            <div className="form-group">
              <label className="form-label" id="settings-theme-label">
                Default Theme
              </label>
              <div
                ref={themeSelectRef}
                className="select-wrap form-select-wrap settings-theme-dropdown"
              >
                <button
                  type="button"
                  className="form-select-trigger"
                  aria-haspopup="listbox"
                  aria-expanded={themeMenuOpen}
                  aria-labelledby="settings-theme-label"
                  onClick={() => setThemeMenuOpen((o) => !o)}
                >
                  <span>
                    {THEME_OPTIONS.find((o) => o.value === themeSelect)?.label ?? themeSelect}
                  </span>
                  <ChevronDown
                    className="form-select-trigger__chevron"
                    aria-hidden
                    strokeWidth={2}
                    size={18}
                  />
                </button>
                {themeMenuOpen ? (
                  <div
                    className="select-menu card settings-theme-dropdown__menu"
                    role="listbox"
                    aria-label="Default theme"
                  >
                    {THEME_OPTIONS.map((opt) => {
                      const active = opt.value === themeSelect;
                      return (
                        <button
                          key={opt.value}
                          type="button"
                          className={['select-item', 'settings-theme-dropdown__item', active ? 'active' : '']
                            .filter(Boolean)
                            .join(' ')}
                          role="option"
                          aria-selected={active}
                          onClick={() => {
                            setThemeSelect(opt.value);
                            setThemeMenuOpen(false);
                          }}
                        >
                          <span className="label">{opt.label}</span>
                          {active ? <Check size={18} strokeWidth={2.5} /> : <span className="spacer" aria-hidden />}
                        </button>
                      );
                    })}
                  </div>
                ) : null}
              </div>
            </div>
          </div>

        </div>

        <div className="card reveal active" id="dashboard-charts">
          <h3 className="card-title" style={{ marginBottom: 24 }}>
            Dashboard charts
          </h3>
          <div id="chart-toggles">
            {charts.map((c, i) => {
              const def = CHART_DEFS.find((d) => d.id === c.id);
              return (
                <div
                  key={c.id}
                  className="settings-toggle-row"
                  style={{
                    borderBottom: i < charts.length - 1 ? '1px solid var(--border-color)' : undefined,
                  }}
                >
                  <span>{def?.label ?? c.id}</span>
                  <label className="switch">
                    <input
                      type="checkbox"
                      checked={c.visible}
                      onChange={(e) => {
                        const next = [...charts];
                        next[i] = { ...c, visible: e.target.checked };
                        setCharts(next);
                      }}
                    />
                    <span className="slider round"></span>
                  </label>
                </div>
              );
            })}
          </div>
        </div>
      </div>
    </div>
  );
}
