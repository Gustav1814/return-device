import { useMemo, useState, useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { motion, AnimatePresence } from 'motion/react';
import { saasAxios } from '../api/saasAxios';
import { useToast } from '../context/ToastContext';
import { applyDocumentTheme } from '../theme/applyDocumentTheme';
import {
  RefreshCw,
  Mail,
  Lock,
  ArrowRight,
  Github,
  Chrome,
  CheckCircle2,
  ShieldCheck,
  Truck,
  CreditCard,
  Moon,
  Sun,
} from 'lucide-react';

export function LoginPage() {
  const navigate = useNavigate();
  const [params] = useSearchParams();
  const { showToast } = useToast();

  const nextPath = useMemo(() => {
    const next = params.get('next');
    return next && next.startsWith('/') ? next : '/dashboard';
  }, [params]);

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [isSuccess, setIsSuccess] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [uiDark, setUiDark] = useState(() => document.documentElement.dataset.theme === 'dark');

  useEffect(() => {
    const q = params.get('email');
    if (q) setEmail(q);
  }, [params]);

  useEffect(() => {
    const onTheme = () => setUiDark(document.documentElement.dataset.theme === 'dark');
    const obs = new MutationObserver(onTheme);
    obs.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
    return () => obs.disconnect();
  }, []);

  function toggleAppearance() {
    const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    applyDocumentTheme(next);
    setUiDark(next === 'dark');
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setIsLoading(true);

    try {
      const body = new URLSearchParams({ email, password });
      await saasAxios.post('/wl-login', body, {
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      });
      setIsSuccess(true);
      setTimeout(() => navigate(nextPath, { replace: true }), 350);
    } catch (err: unknown) {
      const ax = err as { response?: { data?: Record<string, unknown> } };
      const data = ax.response?.data as
        | { message?: string; errors?: { email?: string[]; password?: string[] } }
        | undefined;
      const msg =
        data?.message ||
        data?.errors?.email?.[0] ||
        data?.errors?.password?.[0] ||
        'Login failed.';
      setError(msg);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-slate-50 dark:bg-slate-950 flex items-center justify-center p-6 font-sans selection:bg-emerald-100 dark:selection:bg-emerald-900 selection:text-emerald-900 dark:selection:text-emerald-100">
      <div className="fixed inset-0 overflow-hidden pointer-events-none">
        <div className="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-emerald-100/50 dark:bg-emerald-900/20 rounded-full blur-[120px]" />
        <div className="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-indigo-100/50 dark:bg-indigo-900/20 rounded-full blur-[120px]" />
      </div>

      <button
        type="button"
        onClick={toggleAppearance}
        className="fixed top-4 right-4 z-20 flex items-center gap-2 rounded-full border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-900/90 px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 shadow-sm backdrop-blur-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
        aria-label={uiDark ? 'Switch to light mode' : 'Switch to dark mode'}
      >
        {uiDark ? <Sun className="w-4 h-4" /> : <Moon className="w-4 h-4" />}
        {uiDark ? 'Light' : 'Dark'}
      </button>

      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.6, ease: [0.22, 1, 0.36, 1] }}
        className="w-full max-w-[1100px] grid lg:grid-cols-2 bg-white dark:bg-slate-900 rounded-[32px] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.1)] dark:shadow-[0_32px_64px_-16px_rgba(0,0,0,0.5)] overflow-hidden relative z-10 border border-slate-100 dark:border-slate-800"
      >
        <div className="hidden lg:flex flex-col justify-between p-12 bg-slate-900 text-white relative overflow-hidden">
          <div className="absolute inset-0 opacity-10">
            <div
              className="absolute inset-0"
              style={{
                backgroundImage: 'radial-gradient(circle at 2px 2px, white 1px, transparent 0)',
                backgroundSize: '32px 32px',
              }}
            />
          </div>

          <div className="relative z-10">
            <div className="flex items-center gap-3 mb-12">
              <div className="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20">
                <RefreshCw className="w-6 h-6 text-white" />
              </div>
              <span className="text-2xl font-bold tracking-tight">DeviceReturn</span>
            </div>

            <div className="space-y-8">
              <motion.h2
                initial={{ opacity: 0, x: -20 }}
                animate={{ opacity: 1, x: 0 }}
                transition={{ delay: 0.2 }}
                className="text-4xl font-extrabold leading-tight"
              >
                The modern way to manage <span className="text-emerald-400">IT asset logistics.</span>
              </motion.h2>

              <div className="space-y-6">
                {[
                  {
                    icon: Truck,
                    title: 'Automated Shipping',
                    desc: 'Instant label generation and pickup scheduling.',
                  },
                  {
                    icon: ShieldCheck,
                    title: 'Secure Logistics',
                    desc: 'End-to-end tracking and insurance for every asset.',
                  },
                  {
                    icon: CreditCard,
                    title: 'Easy Payments',
                    desc: 'Integrated processing and transparent pricing.',
                  },
                ].map((feature, i) => (
                  <motion.div
                    key={i}
                    initial={{ opacity: 0, x: -20 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: 0.3 + i * 0.1 }}
                    className="flex gap-4"
                  >
                    <div className="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center flex-shrink-0 border border-white/5">
                      <feature.icon className="w-6 h-6 text-emerald-400" />
                    </div>
                    <div>
                      <h4 className="font-semibold text-lg">{feature.title}</h4>
                      <p className="text-slate-400 text-sm">{feature.desc}</p>
                    </div>
                  </motion.div>
                ))}
              </div>
            </div>
          </div>

          <div className="relative z-10 pt-12 border-t border-white/10">
            <p className="text-slate-400 text-sm">Trusted by 500+ remote-first companies worldwide.</p>
          </div>
        </div>

        <div className="p-8 lg:p-16 flex flex-col justify-center relative bg-white dark:bg-slate-900">
          <AnimatePresence mode="wait">
            {!isSuccess ? (
              <motion.div
                key="login-form"
                initial={{ opacity: 0, x: 20 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: -20 }}
                className="w-full max-w-sm mx-auto"
              >
                <div className="mb-10">
                  <h1 className="text-3xl font-bold text-slate-900 dark:text-slate-100 mb-2">Welcome back</h1>
                  <p className="text-slate-500 dark:text-slate-400">Enter your details to access your dashboard</p>
                </div>

                {error ? (
                  <div className="mb-6 p-3 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-100 dark:border-rose-900 text-rose-700 dark:text-rose-200 text-sm font-semibold">
                    {error}
                  </div>
                ) : null}

                <form onSubmit={handleSubmit} className="space-y-6">
                  <div className="space-y-2">
                    <label className="text-sm font-semibold text-slate-700 dark:text-slate-300 ml-1">
                      Email Address
                    </label>
                    <div className="relative group">
                      <Mail className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-emerald-500 transition-colors" />
                      <input
                        type="email"
                        required
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        placeholder="admin@acme.com"
                        className="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all placeholder:text-slate-400 text-slate-900 dark:text-slate-100"
                      />
                    </div>
                  </div>

                  <div className="space-y-2">
                    <label className="text-sm font-semibold text-slate-700 dark:text-slate-300 ml-1">Password</label>
                    <div className="relative group">
                      <Lock className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-emerald-500 transition-colors" />
                      <input
                        type="password"
                        required
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        placeholder="••••••••"
                        className="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all placeholder:text-slate-400 text-slate-900 dark:text-slate-100"
                      />
                    </div>
                  </div>

                  <button
                    type="submit"
                    disabled={isLoading}
                    className="w-full bg-slate-900 dark:bg-emerald-600 text-white py-4 rounded-2xl font-bold text-lg shadow-xl shadow-slate-900/20 dark:shadow-emerald-900/30 hover:bg-slate-800 dark:hover:bg-emerald-500 active:scale-[0.98] transition-all flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
                  >
                    {isLoading ? (
                      <div className="w-6 h-6 border-3 border-white/30 border-t-white rounded-full animate-spin" />
                    ) : (
                      <>
                        Sign In <ArrowRight className="w-5 h-5" />
                      </>
                    )}
                  </button>
                </form>

                <div className="mt-10">
                  <div className="relative mb-8">
                    <div className="absolute inset-0 flex items-center">
                      <div className="w-full border-t border-slate-200 dark:border-slate-700" />
                    </div>
                    <div className="relative flex justify-center text-sm">
                      <span className="px-4 bg-white dark:bg-slate-900 text-slate-400 font-medium">Or continue with</span>
                    </div>
                  </div>

                  <div className="grid grid-cols-2 gap-4">
                    <button
                      type="button"
                      className="flex items-center justify-center gap-2 py-3 border border-slate-200 dark:border-slate-700 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors font-semibold text-slate-700 dark:text-slate-200"
                      onClick={() => showToast('Google sign-in is not configured for this portal.', 'default')}
                    >
                      <Chrome className="w-5 h-5" /> Google
                    </button>
                    <button
                      type="button"
                      className="flex items-center justify-center gap-2 py-3 border border-slate-200 dark:border-slate-700 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors font-semibold text-slate-700 dark:text-slate-200"
                      onClick={() => showToast('GitHub sign-in is not configured for this portal.', 'default')}
                    >
                      <Github className="w-5 h-5" /> GitHub
                    </button>
                  </div>
                </div>
              </motion.div>
            ) : (
              <motion.div key="success" initial={{ opacity: 0, scale: 0.9 }} animate={{ opacity: 1, scale: 1 }} className="text-center">
                <div className="w-20 h-20 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-6">
                  <CheckCircle2 className="w-10 h-10" />
                </div>
                <h2 className="text-3xl font-bold text-slate-900 dark:text-slate-100 mb-2">Success!</h2>
                <p className="text-slate-500 dark:text-slate-400">Redirecting…</p>
              </motion.div>
            )}
          </AnimatePresence>
        </div>
      </motion.div>
    </div>
  );
}
