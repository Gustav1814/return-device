import { useEffect, useState } from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { saasAxios } from '../api/saasAxios';

export function RequireSaasAuth({ children }: { children: React.ReactNode }) {
  const [state, setState] = useState<'checking' | 'authed' | 'guest'>('checking');
  const location = useLocation();

  useEffect(() => {
    saasAxios
      .get('/api/saas/settings')
      .then(() => setState('authed'))
      .catch(() => setState('guest'));
  }, []);

  if (state === 'checking') {
    return (
      <div
        style={{
          minHeight: '100vh',
          display: 'grid',
          placeItems: 'center',
          color: 'var(--text-muted, #64748b)',
          fontFamily: 'Inter, system-ui, sans-serif',
        }}
      >
        Loading…
      </div>
    );
  }
  if (state === 'guest') {
    const next = encodeURIComponent(location.pathname + location.search);
    return <Navigate to={`/login?next=${next}`} replace />;
  }

  return <>{children}</>;
}

