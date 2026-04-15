/**
 * XAMPP / subdirectory: Blade login posts to the current origin + path, so it always hits Laravel.
 * The SPA uses axios with a base URL — if APP_URL in .env is only `http://localhost` while the app
 * lives at `http://localhost/devicereturn_10/public/saas/...`, requests must use the longer base.
 */

function metaAppUrl(): string {
  return (document.querySelector('meta[name="app-url"]')?.getAttribute('content') ?? '').replace(/\/+$/, '');
}

function metaSaasBasename(): string | null {
  const v = document.querySelector('meta[name="saas-basename"]')?.getAttribute('content')?.trim();
  return v && v.length > 0 ? v.replace(/\/+$/, '') : null;
}

/** Origin + path up to (not including) `/saas` when present in the current URL. */
function computedLaravelBase(): string {
  const { origin, pathname } = window.location;
  const idx = pathname.indexOf('/saas');
  if (idx < 0) return '';
  const basePath = pathname.slice(0, idx).replace(/\/+$/, '');
  return basePath ? origin + basePath : origin;
}

/** Laravel app root (same host the Blade forms use). */
export function getLaravelBaseUrl(): string {
  const meta = metaAppUrl();
  const computed = computedLaravelBase();
  if (computed && meta && computed.length > meta.length) {
    return computed;
  }
  if (computed) {
    return computed;
  }
  return meta || window.location.origin;
}

/** React Router basename (path prefix before in-app routes like `/login`). */
export function getSaasBasename(): string {
  const fromMeta = metaSaasBasename();
  const { pathname } = window.location;
  const idx = pathname.indexOf('/saas');
  const computed = idx >= 0 ? pathname.slice(0, idx + '/saas'.length) : '';
  if (computed && fromMeta && computed.length > fromMeta.length) {
    return computed;
  }
  if (fromMeta) {
    return fromMeta;
  }
  if (computed) {
    return computed;
  }
  return '/saas';
}
