import axios, { type AxiosInstance } from 'axios';
import { getLaravelBaseUrl } from '../runtimeBase';

function readCookie(name: string): string | null {
  const parts = document.cookie.split(';').map((p) => p.trim());
  const found = parts.find((p) => p.startsWith(`${name}=`));
  if (!found) return null;
  return found.slice(name.length + 1);
}

/** Plain XSRF-TOKEN cookie (must be in EncryptCookies $except) — matches session after each response. */
function xsrfTokenPlain(): string | null {
  const raw = readCookie('XSRF-TOKEN');
  if (!raw) return null;
  try {
    return decodeURIComponent(raw);
  } catch {
    return raw;
  }
}

function csrfFromMeta(): string {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

/** Laravel web session + CSRF (same-origin SPA). */
export const saasAxios: AxiosInstance = axios.create({
  // Same Laravel root as Blade forms (critical when APP_URL omits /public on XAMPP).
  baseURL: getLaravelBaseUrl() || undefined,
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    // Do NOT default Content-Type: application/json — axios would JSON-serialize FormData
    // (see defaults transformRequest: isFormData + application/json → formDataToJSON).
    'X-Requested-With': 'XMLHttpRequest',
  },
});

saasAxios.interceptors.request.use((config) => {
  // Laravel reads X-CSRF-TOKEN before X-XSRF-TOKEN. A stale <meta name="csrf-token"> breaks POSTs
  // (e.g. logo upload) while the rotated XSRF-TOKEN cookie is correct — prefer the cookie when present.
  const fromCookie = xsrfTokenPlain();
  if (fromCookie) {
    config.headers['X-CSRF-TOKEN'] = fromCookie;
    delete (config.headers as Record<string, unknown>)['X-XSRF-TOKEN'];
  } else {
    const csrf = csrfFromMeta();
    if (csrf) {
      config.headers['X-CSRF-TOKEN'] = csrf;
    }
  }
  return config;
});
