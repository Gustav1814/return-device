import { useState } from 'react';
import { Check, Copy } from 'lucide-react';
import { useToast } from '../context/ToastContext';

const ENDPOINTS = [
  { method: 'GET', path: '/api/v1/device_returns' },
  { method: 'POST', path: '/api/v1/create-order' },
  { method: 'GET', path: '/api/v1/orders' },
] as const;

const POSTMAN_HREF = '/api-collection/RD-Enterprise-API-Collection.postman_collection.json';

export function ApiDocsPage() {
  const { showToast } = useToast();
  const [copied, setCopied] = useState<string | null>(null);

  async function copyText(label: string, text: string) {
    try {
      await navigator.clipboard.writeText(text);
      setCopied(text);
      showToast(`${label} copied`, 'success');
      window.setTimeout(() => setCopied((c) => (c === text ? null : c)), 2000);
    } catch {
      showToast('Could not copy — select the text manually.', 'error');
    }
  }

  return (
    <div className="content-wrapper">
      <div className="page-header reveal active">
        <h1 className="page-title">API Integration</h1>
        <div className="toolbar">
          <a
            href={POSTMAN_HREF}
            className="top-bar-page-chip top-bar-page-chip--link"
            download
          >
            <span className="top-bar-page-chip__shine" aria-hidden />
            <span className="top-bar-page-chip__text">Download Postman collection</span>
          </a>
        </div>
      </div>

      <div className="card reveal active">
        <p style={{ color: 'var(--text-muted)', marginBottom: 16 }}>
          Use your existing API v1 endpoints under <code>/api/v1</code>. Authenticate with your API key from the
          classic portal if required.
        </p>
        <p style={{ color: 'var(--text-muted)', marginBottom: 16, fontSize: 14 }}>
          The SaaS dashboard uses session-based JSON under <code>/api/saas/*</code> (same browser session as{' '}
          <code>/wl-login</code>), for example <code>GET /api/saas/me</code> and <code>GET /api/saas/settings</code>.
        </p>
        <ul style={{ margin: 0, paddingLeft: 0, listStyle: 'none' }}>
          {ENDPOINTS.map(({ method, path }) => {
            const line = `${method} ${path}`;
            const isCopied = copied === line;
            return (
              <li
                key={line}
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: 12,
                  flexWrap: 'wrap',
                  padding: '10px 0',
                  borderBottom: '1px solid var(--border-color)',
                }}
              >
                <code style={{ flex: '1 1 200px', fontSize: 14 }}>{line}</code>
                <button
                  type="button"
                  className="btn btn-outline"
                  style={{ padding: '6px 12px', fontSize: 13 }}
                  onClick={() => copyText(line, line)}
                >
                  {isCopied ? (
                    <>
                      <Check size={16} /> Copied
                    </>
                  ) : (
                    <>
                      <Copy size={16} /> Copy
                    </>
                  )}
                </button>
              </li>
            );
          })}
        </ul>
        <p style={{ marginTop: 20, fontSize: 13, color: 'var(--text-muted)' }}>
          Import the Postman collection JSON for a ready-made request set compatible with your environment.
        </p>
      </div>
    </div>
  );
}
