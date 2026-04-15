import { useCallback, useEffect, useMemo, useState } from 'react';
import { saasAxios } from '../api/saasAxios';
import { applyDocumentTheme, type UiTheme } from '../theme/applyDocumentTheme';
import {
  chartSecondaryFromPrimary,
  clampAccentForUiTheme,
  isAchromaticPrimary,
  normalizeHex,
} from '../utils/chartTheme';

export type SaasSettings = {
  id?: number;
  company_id?: number;
  logo?: string | null;
  btn_bg_color?: string | null;
  btn_font_color?: string | null;
  theme_bg_color?: string | null;
  theme_font_color?: string | null;
  settings_data?: any;
};

function hexToRgb(hex: string): { r: number; g: number; b: number } | null {
  const m = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex.trim());
  if (!m) return null;
  return { r: parseInt(m[1], 16), g: parseInt(m[2], 16), b: parseInt(m[3], 16) };
}

function rgbToHex(rgb: { r: number; g: number; b: number }) {
  const h = (n: number) => n.toString(16).padStart(2, '0');
  return `#${h(rgb.r)}${h(rgb.g)}${h(rgb.b)}`;
}

/** Lighter tint of primary for mesh, cards, and global `--grad-primary` — keeps selected theme cohesive. */
function mixTowardWhite(rgb: { r: number; g: number; b: number }, t: number) {
  return {
    r: Math.round(rgb.r + (255 - rgb.r) * t),
    g: Math.round(rgb.g + (255 - rgb.g) * t),
    b: Math.round(rgb.b + (255 - rgb.b) * t),
  };
}

function resolveThemeFromSettings(settings: SaasSettings | null): UiTheme {
  if (!settings) {
    try {
      const t = localStorage.getItem('saas-last-ui-theme');
      if (t === 'dark' || t === 'light') return t;
    } catch {
      /* ignore */
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }
  const sd = settings.settings_data;
  if (sd?.themePreference === 'system') {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }
  return sd?.theme === 'dark' ? 'dark' : 'light';
}

export function useSaasSettings() {
  const [settings, setSettings] = useState(null as SaasSettings | null);
  const [loading, setLoading] = useState(true);
  const [systemTick, setSystemTick] = useState(0);

  const reloadSettings = useCallback(() => {
    setLoading(true);
    saasAxios
      .get('/api/saas/settings')
      .then((r) => setSettings(r.data))
      .catch(() => setSettings(null))
      .finally(() => setLoading(false));
  }, []);

  useEffect(() => {
    reloadSettings();
  }, [reloadSettings]);

  useEffect(() => {
    if (settings?.settings_data?.themePreference !== 'system') return;
    const mq = window.matchMedia('(prefers-color-scheme: dark)');
    const onChange = () => setSystemTick((n) => n + 1);
    mq.addEventListener('change', onChange);
    return () => mq.removeEventListener('change', onChange);
  }, [settings?.settings_data?.themePreference]);

  const theme = useMemo(
    () => resolveThemeFromSettings(settings),
    [settings, systemTick],
  );

  useEffect(() => {
    applyDocumentTheme(theme);
  }, [theme]);

  useEffect(() => {
    if (!settings) return;
    const sd = settings.settings_data ?? {};
    const rawUser =
      (typeof sd.primaryColorUser === 'string' &&
        sd.primaryColorUser.startsWith('#') &&
        sd.primaryColorUser.trim() &&
        sd.primaryColorUser) ||
      (typeof settings.btn_bg_color === 'string' &&
        settings.btn_bg_color.startsWith('#') &&
        settings.btn_bg_color.trim() &&
        settings.btn_bg_color) ||
      (typeof sd.primaryColor === 'string' && sd.primaryColor.startsWith('#') && sd.primaryColor.trim() && sd.primaryColor) ||
      '';
    if (!rawUser) return;
    const userNorm = normalizeHex(rawUser) ?? rawUser;
    const hex = clampAccentForUiTheme(userNorm, theme);
    document.documentElement.style.setProperty('--brand-primary', hex);
    const rgb = hexToRgb(hex);
    if (rgb) {
      document.documentElement.style.setProperty('--brand-primary-rgb', `${rgb.r}, ${rgb.g}, ${rgb.b}`);
      const soft = mixTowardWhite(rgb, 0.22);
      const accentHex = rgbToHex(soft);
      document.documentElement.style.setProperty('--brand-accent', accentHex);
      document.documentElement.style.setProperty('--brand-accent-rgb', `${soft.r}, ${soft.g}, ${soft.b}`);
      /* Primary buttons only: vivid second stop when primary is gray; otherwise match soft theme accent */
      const ctaHex = isAchromaticPrimary(hex)
        ? chartSecondaryFromPrimary(hex, theme === 'dark')
        : accentHex;
      document.documentElement.style.setProperty('--brand-accent-cta', ctaHex);
      const ctaRgb = hexToRgb(ctaHex);
      if (ctaRgb) {
        document.documentElement.style.setProperty(
          '--brand-accent-cta-rgb',
          `${ctaRgb.r}, ${ctaRgb.g}, ${ctaRgb.b}`,
        );
      }
    }
  }, [settings, theme]);

  async function setTheme(nextTheme: UiTheme) {
    const prev = resolveThemeFromSettings(settings);
    applyDocumentTheme(nextTheme);
    try {
      const { data: updated } = await saasAxios.put('/api/saas/settings', {
        settings_data: {
          theme: nextTheme,
          themePreference: nextTheme,
        },
      });
      setSettings(updated);
    } catch {
      applyDocumentTheme(prev);
    }
  }

  return { settings, loading, theme, setTheme, reloadSettings };
}
