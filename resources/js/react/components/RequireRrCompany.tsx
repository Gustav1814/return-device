import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { saasAxios } from '../api/saasAxios';

type Me = { is_rr_company?: boolean };

/**
 * Classic sidebar only exposes Companies, Coupons, and Commission when `company_id == RR_COMPANY_ID`.
 * Redirect partner tenants away from those React routes (same policy as `header.blade.php`).
 */
export function RequireRrCompany({ children }: { children: React.ReactNode }) {
  const navigate = useNavigate();
  const [allowed, setAllowed] = useState<boolean | null>(null);

  useEffect(() => {
    let cancelled = false;
    saasAxios
      .get<Me>('/api/saas/me')
      .then((r) => {
        if (cancelled) return;
        if (r.data?.is_rr_company) {
          setAllowed(true);
        } else {
          setAllowed(false);
          navigate('/dashboard', { replace: true });
        }
      })
      .catch(() => {
        if (cancelled) return;
        setAllowed(false);
        navigate('/dashboard', { replace: true });
      });
    return () => {
      cancelled = true;
    };
  }, [navigate]);

  if (allowed !== true) {
    return (
      <div className="content-wrapper" style={{ padding: 24, color: 'var(--text-muted)' }}>
        Loading…
      </div>
    );
  }

  return <>{children}</>;
}
