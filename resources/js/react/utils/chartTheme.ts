/**
 * Brand / chart colors: parse hex, WCAG luminance, accent validation per UI mode,
 * and a distinct secondary hue for paired series (e.g. margin vs revenue).
 */

export function parseHexColor(input: string): { r: number; g: number; b: number } | null {
  let h = (input || '').trim().replace(/^#/, '');
  if (h.length === 3) {
    h = h
      .split('')
      .map((c) => c + c)
      .join('');
  }
  if (!/^[0-9a-fA-F]{6}$/.test(h)) return null;
  return {
    r: parseInt(h.slice(0, 2), 16),
    g: parseInt(h.slice(2, 4), 16),
    b: parseInt(h.slice(4, 6), 16),
  };
}

export function normalizeHex(input: string): string | null {
  const rgb = parseHexColor(input);
  if (!rgb) return null;
  const x = (n: number) => n.toString(16).padStart(2, '0');
  return `#${x(rgb.r)}${x(rgb.g)}${x(rgb.b)}`;
}

export function relativeLuminance(rgb: { r: number; g: number; b: number }): number {
  const lin = (v: number) => {
    const x = v / 255;
    return x <= 0.03928 ? x / 12.92 : Math.pow((x + 0.055) / 1.055, 2.4);
  };
  const r = lin(rgb.r);
  const g = lin(rgb.g);
  const b = lin(rgb.b);
  return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}

/** Too light to see on light card backgrounds */
export function isAccentTooLightForLightMode(hex: string): boolean {
  const rgb = parseHexColor(hex);
  if (!rgb) return false;
  return relativeLuminance(rgb) > 0.88;
}

/** Too dark to see on dark card backgrounds */
export function isAccentTooDarkForDarkMode(hex: string): boolean {
  const rgb = parseHexColor(hex);
  if (!rgb) return false;
  return relativeLuminance(rgb) < 0.12;
}

/** Too light / low-contrast as a primary accent on dark UI (e.g. near-white). */
export function isAccentTooLightForDarkMode(hex: string): boolean {
  const rgb = parseHexColor(hex);
  if (!rgb) return false;
  return relativeLuminance(rgb) > 0.88;
}

function mixRgb(
  a: { r: number; g: number; b: number },
  b: { r: number; g: number; b: number },
  t: number,
): { r: number; g: number; b: number } {
  return {
    r: Math.round(a.r + (b.r - a.r) * t),
    g: Math.round(a.g + (b.g - a.g) * t),
    b: Math.round(a.b + (b.b - a.b) * t),
  };
}

function rgbToHex(rgb: { r: number; g: number; b: number }): string {
  const x = (n: number) => Math.max(0, Math.min(255, Math.round(n))).toString(16).padStart(2, '0');
  return `#${x(rgb.r)}${x(rgb.g)}${x(rgb.b)}`;
}

/**
 * Readable accent for the active UI theme. Preserves hue where possible; does not block “illegal” picks in the UI.
 */
export function clampAccentForUiTheme(hex: string, mode: 'light' | 'dark'): string {
  const norm = normalizeHex(hex);
  const rgb = norm ? parseHexColor(norm) : null;
  if (!rgb) {
    return mode === 'dark' ? '#94a3b8' : '#10b981';
  }
  const white = { r: 255, g: 255, b: 255 };
  const black = { r: 0, g: 0, b: 0 };
  let r = rgb.r;
  let g = rgb.g;
  let b = rgb.b;
  const iter = 28;
  if (mode === 'light') {
    for (let i = 0; i < iter; i++) {
      const L = relativeLuminance({ r, g, b });
      if (L <= 0.88) break;
      const m = mixRgb({ r, g, b }, black, 0.2);
      r = m.r;
      g = m.g;
      b = m.b;
    }
  } else {
    for (let i = 0; i < iter; i++) {
      const L = relativeLuminance({ r, g, b });
      if (L >= 0.12 && L <= 0.88) break;
      if (L < 0.12) {
        const m = mixRgb({ r, g, b }, white, 0.22);
        r = m.r;
        g = m.g;
        b = m.b;
      } else {
        const m = mixRgb({ r, g, b }, black, 0.18);
        r = m.r;
        g = m.g;
        b = m.b;
      }
    }
  }
  return rgbToHex({ r, g, b });
}

/** True when the saved / preview accent differs from the hex because of clamping. */
export function accentIsClampedForUiTheme(hex: string, mode: 'light' | 'dark'): boolean {
  const norm = normalizeHex(hex);
  if (!norm) return false;
  return clampAccentForUiTheme(norm, mode).toLowerCase() !== norm.toLowerCase();
}

export function validateAccentForUiTheme(hex: string, mode: 'light' | 'dark'): string | null {
  const norm = normalizeHex(hex);
  if (!norm) return null;
  if (mode === 'light' && isAccentTooLightForLightMode(norm)) {
    return 'This accent is too close to white for light mode. Choose a darker color.';
  }
  if (mode === 'dark' && isAccentTooDarkForDarkMode(norm)) {
    return 'This accent is too close to black for dark mode. Choose a lighter color.';
  }
  return null;
}

function rgbToHsl(r: number, g: number, b: number): { h: number; s: number; l: number } {
  r /= 255;
  g /= 255;
  b /= 255;
  const max = Math.max(r, g, b);
  const min = Math.min(r, g, b);
  const d = max - min;
  let h = 0;
  if (d !== 0) {
    if (max === r) h = ((g - b) / d + (g < b ? 6 : 0)) / 6;
    else if (max === g) h = ((b - r) / d + 2) / 6;
    else h = ((r - g) / d + 4) / 6;
  }
  const l = (max + min) / 2;
  const s = d === 0 ? 0 : d / (1 - Math.abs(2 * l - 1));
  return { h: h * 360, s, l };
}

function hslToRgb(h: number, s: number, l: number): { r: number; g: number; b: number } {
  const c = (1 - Math.abs(2 * l - 1)) * s;
  const x = c * (1 - Math.abs(((h / 60) % 2) - 1));
  const m = l - c / 2;
  let rp = 0,
    gp = 0,
    bp = 0;
  if (h < 60) {
    rp = c;
    gp = x;
  } else if (h < 120) {
    rp = x;
    gp = c;
  } else if (h < 180) {
    gp = c;
    bp = x;
  } else if (h < 240) {
    gp = x;
    bp = c;
  } else if (h < 300) {
    rp = x;
    bp = c;
  } else {
    rp = c;
    bp = x;
  }
  return {
    r: Math.round((rp + m) * 255),
    g: Math.round((gp + m) * 255),
    b: Math.round((bp + m) * 255),
  };
}

/** True when primary has almost no saturation (gray / near-gray). */
export function isAchromaticPrimary(hex: string, satThreshold = 0.07): boolean {
  const rgb = parseHexColor(hex);
  if (!rgb) return true;
  const { s } = rgbToHsl(rgb.r, rgb.g, rgb.b);
  return s < satThreshold;
}

/** Second series color: rotate hue so margin reads distinct from revenue but stays on-brand. */
export function chartSecondaryFromPrimary(primaryHex: string, isDark: boolean): string {
  const rgb = parseHexColor(primaryHex);
  if (!rgb) return isDark ? '#34d399' : '#059669';
  const { h, s, l } = rgbToHsl(rgb.r, rgb.g, rgb.b);
  const h2 = (h + 148) % 360;
  const s2 = Math.min(0.92, Math.max(0.35, s || 0.55));
  const l2 = isDark ? Math.min(0.62, Math.max(0.42, l)) : Math.min(0.48, Math.max(0.28, l));
  return rgbToHex(hslToRgb(h2, s2, l2));
}

export function chartAxisColors(isDark: boolean): { tick: string; grid: string } {
  if (isDark) {
    return {
      tick: 'rgba(203, 213, 225, 0.82)',
      grid: 'rgba(148, 163, 184, 0.12)',
    };
  }
  return {
    tick: '#64748b',
    grid: 'rgba(148, 163, 184, 0.14)',
  };
}

export function chartTooltipTheme(isDark: boolean): { bg: string; titleColor: string; bodyColor: string } {
  if (isDark) {
    return {
      bg: 'rgba(15, 23, 42, 0.94)',
      titleColor: '#f1f5f9',
      bodyColor: '#e2e8f0',
    };
  }
  return {
    bg: 'rgba(15, 23, 42, 0.9)',
    titleColor: '#fff',
    bodyColor: '#f1f5f9',
  };
}

/** Palette for doughnut: anchor slices to primary + rotated hues */
export function chartDistributionPalette(primaryHex: string, count: number, isDark: boolean): string[] {
  const base = parseHexColor(primaryHex);
  if (!base || count <= 0) {
    const fallback = ['#6366f1', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#84cc16', '#f97316'];
    return Array.from({ length: count }, (_, i) => fallback[i % fallback.length]);
  }
  const { h, s, l } = rgbToHsl(base.r, base.g, base.b);
  const out: string[] = [];
  const step = 360 / Math.max(8, count);
  for (let i = 0; i < count; i++) {
    const hi = (h + i * step) % 360;
    const si = Math.min(0.88, Math.max(0.38, s || 0.55));
    const li = isDark ? Math.min(0.58, Math.max(0.4, l)) : Math.min(0.52, Math.max(0.32, l));
    out.push(rgbToHex(hslToRgb(hi, si, li)));
  }
  return out;
}
