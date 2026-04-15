export type UiTheme = 'light' | 'dark';

const STORAGE_KEY = 'saas-last-ui-theme';

export function applyDocumentTheme(mode: UiTheme): void {
  document.documentElement.dataset.theme = mode;
  document.documentElement.classList.toggle('dark', mode === 'dark');
  try {
    localStorage.setItem(STORAGE_KEY, mode);
  } catch {
    /* private mode */
  }
}

/** Before React mounts — matches post-login preference when possible. */
export function bootstrapDocumentTheme(): void {
  try {
    const t = localStorage.getItem(STORAGE_KEY);
    if (t === 'light' || t === 'dark') {
      applyDocumentTheme(t);
      return;
    }
  } catch {
    /* ignore */
  }
  applyDocumentTheme(window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
}

export function readStoredUiTheme(): UiTheme | null {
  try {
    const t = localStorage.getItem(STORAGE_KEY);
    if (t === 'light' || t === 'dark') return t;
  } catch {
    /* ignore */
  }
  return null;
}
