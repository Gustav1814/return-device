import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { ArrowLeft, ChevronDown, Package, Truck } from 'lucide-react';
import { saasAxios } from '../api/saasAxios';
import { US_STATES } from '../data/usStates';
import { useSaasSettings } from '../hooks/useSaasSettings';
import { applyDocumentTheme } from '../theme/applyDocumentTheme';

type OrderConfig = {
  insurance_rate_percent: number;
  dd_company: number;
  dd_new_emp: number;
  company_settings_id: number;
  env_company_setting_id: number | null;
  is_rr_default_company_settings: boolean;
  tenant_company_id: number;
  remote_recipient: {
    company_name: string | null;
    company_email: string | null;
    company_phone: string | null;
    company_add_1: string | null;
    company_add_2: string | null;
    company_city: string | null;
    company_state: string | null;
    company_zip: string | null;
    comp_receip_name: string | null;
  };
};

function csrfToken(): string {
  return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
}

function randomPassword(): string {
  return btoa(Math.random().toString(36).slice(2, 12)).slice(0, 10);
}

function onlyDigits(value: string): string {
  return value.replace(/\D/g, '');
}

function formatCardNumber(value: string): string {
  const digits = onlyDigits(value).slice(0, 16);
  return digits.replace(/(.{4})/g, '$1 ').trim();
}

function sanitizeCardholderName(value: string): string {
  return value.replace(/[^A-Za-z\s'.-]/g, '').replace(/\s{2,}/g, ' ').slice(0, 60);
}

function isValidCardholderName(value: string): boolean {
  return /^[A-Za-z][A-Za-z\s'.-]{1,59}$/.test(value.trim());
}

function isValidCardNumber(value: string): boolean {
  const digits = onlyDigits(value);
  if (digits.length !== 16) return false;

  // Luhn checksum validation
  let sum = 0;
  let shouldDouble = false;
  for (let i = digits.length - 1; i >= 0; i -= 1) {
    let d = Number(digits[i]);
    if (shouldDouble) {
      d *= 2;
      if (d > 9) d -= 9;
    }
    sum += d;
    shouldDouble = !shouldDouble;
  }
  return sum % 10 === 0;
}

function isValidExpiry(value: string): boolean {
  const match = value.trim().match(/^(\d{4})-(0[1-9]|1[0-2])$/);
  if (!match) return false;

  const year = Number(match[1]);
  const month = Number(match[2]);
  const now = new Date();
  const currentYear = now.getFullYear();
  const currentMonth = now.getMonth() + 1;
  return year > currentYear || (year === currentYear && month >= currentMonth);
}

function formatExpiryYearMonth(value: string): string {
  const digits = onlyDigits(value).slice(0, 6);
  if (digits.length <= 4) return digits;
  return `${digits.slice(0, 4)}-${digits.slice(4)}`;
}

type SelectOption = { value: string; label: string };

function ThemedSelect({
  value,
  options,
  onChange,
  placeholder,
  disabled,
  uiDark,
}: {
  value: string;
  options: SelectOption[];
  onChange: (v: string) => void;
  placeholder: string;
  disabled?: boolean;
  uiDark?: boolean;
}) {
  const [open, setOpen] = useState(false);
  const rootRef = useRef<HTMLDivElement | null>(null);
  const selected = options.find((o) => o.value === value);

  useEffect(() => {
    const onDocClick = (ev: MouseEvent) => {
      if (!rootRef.current) return;
      if (!rootRef.current.contains(ev.target as Node)) setOpen(false);
    };
    document.addEventListener('mousedown', onDocClick);
    return () => document.removeEventListener('mousedown', onDocClick);
  }, []);

  return (
    <div ref={rootRef} style={{ position: 'relative' }}>
      <button
        type="button"
        className="form-input"
        disabled={disabled}
        onClick={() => !disabled && setOpen((s) => !s)}
        style={{
          width: '100%',
          textAlign: 'left',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          backgroundColor: uiDark ? 'rgba(14, 14, 14, 0.95)' : '#ffffff',
          border: uiDark ? '1px solid rgba(255,255,255,0.14)' : '1px solid var(--border-color)',
          color: uiDark ? '#e5e7eb' : 'var(--text-main)',
        }}
      >
        <span>{selected?.label ?? placeholder}</span>
        <ChevronDown size={16} style={{ opacity: 0.8 }} />
      </button>
      {open && !disabled ? (
        <div
          style={{
            position: 'absolute',
            zIndex: 50,
            left: 0,
            right: 0,
            marginTop: 6,
            maxHeight: 220,
            overflowY: 'auto',
            borderRadius: 10,
            border: uiDark ? '1px solid rgba(255,255,255,0.14)' : '1px solid var(--border-color)',
            background: uiDark ? '#0b0b0b' : '#ffffff',
            boxShadow: uiDark
              ? '0 12px 30px rgba(0,0,0,0.5)'
              : '0 12px 24px rgba(15,23,42,0.12)',
          }}
        >
          {options.map((opt) => {
            const active = opt.value === value;
            return (
              <button
                key={opt.value}
                type="button"
                onClick={() => {
                  onChange(opt.value);
                  setOpen(false);
                }}
                style={{
                  width: '100%',
                  textAlign: 'left',
                  padding: '10px 12px',
                  border: 'none',
                  background: active
                    ? uiDark
                      ? 'rgba(var(--brand-primary-rgb, 99, 102, 241), 0.24)'
                      : 'rgba(var(--brand-primary-rgb, 99, 102, 241), 0.16)'
                    : 'transparent',
                  color: uiDark ? '#e5e7eb' : '#0f172a',
                  cursor: 'pointer',
                }}
              >
                {opt.label}
              </button>
            );
          })}
        </div>
      ) : null}
    </div>
  );
}

function StateSelect({
  id,
  value,
  onChange,
  required,
  disabled,
  uiDark,
}: {
  id: string;
  value: string;
  onChange: (v: string) => void;
  required?: boolean;
  disabled?: boolean;
  uiDark?: boolean;
}) {
  return (
    <div id={id}>
      <ThemedSelect
        value={value}
        onChange={onChange}
        uiDark={uiDark}
        disabled={disabled}
        placeholder="State / Province"
        options={US_STATES.map((s) => ({ value: s.code, label: s.name }))}
      />
      {required && !disabled ? (
        <input
          value={value}
          onChange={() => {}}
          required
          tabIndex={-1}
          aria-hidden="true"
          style={{ position: 'absolute', opacity: 0, pointerEvents: 'none', width: 0, height: 0 }}
        />
      ) : null}
    </div>
  );
}

export function PublicOrderPage() {
  const { setTheme: persistDashboardTheme } = useSaasSettings();
  const [params] = useSearchParams();
  const [config, setConfig] = useState<OrderConfig | null>(null);
  const [configErr, setConfigErr] = useState<string | null>(null);
  const [uiDark, setUiDark] = useState(() => document.documentElement.dataset.theme === 'dark');

  const [step, setStep] = useState(0);

  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  /** True after /api/saas/me when user is logged in but `users.phone` is empty (not a UI bug). */
  const [accountMissingPhoneHint, setAccountMissingPhoneHint] = useState(false);
  /** Header: show Log in vs Dashboard (mutually exclusive). */
  const [sessionState, setSessionState] = useState<'checking' | 'authed' | 'guest'>('checking');
  const [typeOfEquipment, setTypeOfEquipment] = useState(
    () => (params.get('monitor') !== null ? 'Monitor' : 'Laptop'),
  );
  const [orderType, setOrderType] = useState<'Return To Company' | 'Sell This Equipment'>(
    'Return To Company',
  );
  const [insTick, setInsTick] = useState(false);
  const [insAmount, setInsAmount] = useState('');

  const [empFirst, setEmpFirst] = useState('');
  const [empLast, setEmpLast] = useState('');
  const [empEmail, setEmpEmail] = useState('');
  const [empPhone, setEmpPhone] = useState('');
  const [empAdd1, setEmpAdd1] = useState('');
  const [empAdd2, setEmpAdd2] = useState('');
  const [empCity, setEmpCity] = useState('');
  const [empState, setEmpState] = useState('');
  const [empZip, setEmpZip] = useState('');
  const [empCustomMsg, setEmpCustomMsg] = useState('');

  const [coName, setCoName] = useState('');
  const [coRecipName, setCoRecipName] = useState('');
  const [coEmail, setCoEmail] = useState('');
  const [coPhone, setCoPhone] = useState('');
  const [coAdd1, setCoAdd1] = useState('');
  const [coAdd2, setCoAdd2] = useState('');
  const [coCity, setCoCity] = useState('');
  const [coState, setCoState] = useState('');
  const [coZip, setCoZip] = useState('');

  const [cardName, setCardName] = useState('');
  const [cardNo, setCardNo] = useState('');
  const [cardExpiry, setCardExpiry] = useState('');
  const [cardCvv, setCardCvv] = useState('');
  const [couponDraft, setCouponDraft] = useState('');
  const [cpn, setCpn] = useState('');
  const [fcpn, setFcpn] = useState(0);
  const [couponMsg, setCouponMsg] = useState<string | null>(null);

  const [deviceAmt, setDeviceAmt] = useState(0);
  const [totalPay, setTotalPay] = useState(0);

  const [submitting, setSubmitting] = useState(false);
  const [formError, setFormError] = useState<string | null>(null);

  const paymentWaived = fcpn === 1 || (config?.is_rr_default_company_settings && orderType === 'Return To Company');
  const lockAutofilledEmail = sessionState === 'authed' && email.trim() !== '';
  const lockAutofilledPhone = sessionState === 'authed' && phone.trim() !== '';
  const lockCompanyDestination =
    orderType === 'Sell This Equipment' || (orderType === 'Return To Company' && sessionState === 'authed');
  const insComputed = useMemo(() => {
    if (!insTick || !config) return 0;
    const raw = parseFloat(insAmount);
    if (Number.isNaN(raw) || raw <= 0) return 0;
    return (raw * config.insurance_rate_percent) / 100;
  }, [insTick, insAmount, config]);

  useEffect(() => {
    const onTheme = () => setUiDark(document.documentElement.dataset.theme === 'dark');
    const obs = new MutationObserver(onTheme);
    obs.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
    return () => obs.disconnect();
  }, []);

  useEffect(() => {
    saasAxios
      .get<OrderConfig>('/order/config')
      .then((r) => setConfig(r.data))
      .catch(() => setConfigErr('Unable to load order options. Try again later.'));
  }, []);

  useEffect(() => {
    saasAxios
      .get<{ email?: string; phone?: string | null }>('/api/saas/me')
      .then((r) => {
        setSessionState('authed');
        const hasEmail = !!(r.data.email && String(r.data.email).trim() !== '');
        const p = r.data.phone;
        const hasPhone = p != null && String(p).trim() !== '';
        setAccountMissingPhoneHint(hasEmail && !hasPhone);
        if (r.data.email) setEmail(String(r.data.email).trim());
        if (hasPhone) setPhone(String(p).trim());
      })
      .catch(() => {
        setSessionState('guest');
        setAccountMissingPhoneHint(false);
      });
  }, []);

  const refreshDeviceAmount = useCallback(async () => {
    if (!typeOfEquipment) return;
    try {
      const { data } = await saasAxios.get<{ amount?: number }>('/get-order-amount', {
        params: { type_of_equipment: typeOfEquipment },
      });
      const a = typeof data.amount === 'number' ? data.amount : parseFloat(String(data.amount ?? 0));
      setDeviceAmt(Number.isNaN(a) ? 0 : a);
    } catch {
      setDeviceAmt(0);
    }
  }, [typeOfEquipment]);

  useEffect(() => {
    void refreshDeviceAmount();
  }, [refreshDeviceAmount]);

  useEffect(() => {
    setTotalPay(deviceAmt + insComputed);
  }, [deviceAmt, insComputed]);

  const applyRemoteRecipient = useCallback(() => {
    if (!config) return;
    const r = config.remote_recipient;
    setCoName(r.company_name ?? '');
    setCoEmail(r.company_email ?? '');
    setCoPhone(r.company_phone ?? '');
    setCoAdd1(r.company_add_1 ?? '');
    setCoAdd2(r.company_add_2 ?? '');
    setCoCity(r.company_city ?? '');
    setCoState(r.company_state ?? '');
    setCoZip(r.company_zip ?? '');
    setCoRecipName(r.comp_receip_name ?? '');
  }, [config]);

  useEffect(() => {
    if (step !== 3 || !config) return;
    if (orderType === 'Sell This Equipment') {
      applyRemoteRecipient();
    }
  }, [step, orderType, config, applyRemoteRecipient]);

  useEffect(() => {
    if (step !== 3 || !config || orderType !== 'Return To Company') return;
    if (sessionState !== 'authed') return;
    const fd = new URLSearchParams();
    fd.set('_token', csrfToken());
    fd.set('cid', String(config.tenant_company_id));
    saasAxios
      .post('/getcompanydetails', fd, {
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      })
      .then((res) => {
        const d = res.data as { status?: string; details?: Record<string, string> };
        if (d.status !== 'success' || !d.details) return;
        const c = d.details;
        setCoName(String(c.company_name ?? ''));
        setCoEmail(String(c.company_email ?? ''));
        setCoPhone(String(c.company_phone ?? ''));
        setCoAdd1(String(c.company_add_1 ?? ''));
        setCoAdd2(String(c.company_add_2 ?? ''));
        setCoCity(String(c.company_city ?? ''));
        setCoState(String(c.company_state ?? ''));
        setCoZip(String(c.company_zip ?? ''));
        setCoRecipName(String(c.receipient_name ?? ''));
      })
      .catch(() => {});
  }, [step, orderType, config, sessionState]);

  function toggleAppearance() {
    const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    if (sessionState === 'authed') {
      void persistDashboardTheme(next);
    } else {
      applyDocumentTheme(next);
    }
    setUiDark(next === 'dark');
  }

  function nextFromAccount() {
    setFormError(null);
    if (!email.trim() || !phone.trim() || !typeOfEquipment || !orderType) {
      setFormError('Please complete all required fields.');
      return;
    }
    if (insTick) {
      const n = parseFloat(insAmount);
      if (Number.isNaN(n) || n <= 0) {
        setFormError('Enter a valid insurance declared value.');
        return;
      }
    }
    setStep(1);
  }

  function nextFromEmployee() {
    setFormError(null);
    if (!empFirst.trim() || !empLast.trim() || !empEmail.trim() || !empPhone.trim()) {
      setFormError('Employee contact fields are required.');
      return;
    }
    if (!empAdd1.trim() || !empCity.trim() || !empState || !empZip.trim()) {
      setFormError('Employee address is required.');
      return;
    }
    setStep(3);
  }

  function back() {
    setFormError(null);
    if (step === 3) {
      setStep(1);
    } else if (step === 1) {
      setStep(0);
    }
  }

  async function applyCoupon() {
    setCouponMsg(null);
    if (!couponDraft.trim()) {
      setCouponMsg('Enter a coupon code.');
      return;
    }
    if (!coName.trim()) {
      setCouponMsg('Company name is required before applying a coupon.');
      return;
    }
    const q = new URLSearchParams({
      company_name: coName.trim(),
      coupon: couponDraft.trim(),
      amount: String(deviceAmt),
      email: email.trim(),
    });
    if (insTick && insAmount) q.set('insurance', insAmount);
    try {
      const { data } = await saasAxios.get<{
        status: string;
        message?: string;
        totalAmt?: number | string;
        coupon?: { coupon?: string; freeall?: number };
        insurance?: number;
        dd_amt?: number;
      }>(`/get-discount-bycoupon?${q.toString()}`);
      if (data.status === 'success') {
        const t =
          typeof data.totalAmt === 'number' ? data.totalAmt : parseFloat(String(data.totalAmt ?? 0));
        setTotalPay(Number.isNaN(t) ? totalPay : t);
        setCpn(data.coupon?.coupon ?? couponDraft.trim());
        const free =
          data.coupon?.freeall === 1 &&
          (data.insurance ?? 0) === 0 &&
          (data.dd_amt === undefined || data.dd_amt === 0);
        setFcpn(free ? 1 : 0);
        if (free) {
          setCardName('');
          setCardNo('');
          setCardExpiry('');
          setCardCvv('');
        }
        setCouponMsg(data.message ?? 'Coupon applied.');
      } else {
        setCouponMsg(data.message ?? 'Coupon could not be applied.');
      }
    } catch {
      setCouponMsg('Coupon request failed.');
    }
  }

  async function submitOrder(e: { preventDefault: () => void }) {
    e.preventDefault();
    setFormError(null);
    if (!config) return;

    if (!paymentWaived) {
      if (!cardName.trim() || !cardNo.trim() || !cardExpiry.trim() || !cardCvv.trim()) {
        setFormError('Payment details are required.');
        return;
      }
      if (!isValidCardholderName(cardName)) {
        setFormError('Name on card must contain letters only.');
        return;
      }
      if (!isValidCardNumber(cardNo)) {
        setFormError('Card number is invalid.');
        return;
      }
      if (!isValidExpiry(cardExpiry)) {
        setFormError('Expiry must be valid and in YYYY-MM format.');
        return;
      }
      if (!/^\d{3,4}$/.test(onlyDigits(cardCvv))) {
        setFormError('CVC must be 3 or 4 digits.');
        return;
      }
    }

    if (!paymentWaived && totalPay <= 0) {
      setFormError('Invalid total amount.');
      return;
    }

    const recipient =
      orderType === 'Sell This Equipment'
        ? {
            company_name: config.remote_recipient.company_name ?? '',
            company_email: config.remote_recipient.company_email ?? '',
            company_phone: config.remote_recipient.company_phone ?? '',
            company_add_1: config.remote_recipient.company_add_1 ?? '',
            company_add_2: config.remote_recipient.company_add_2 ?? '',
            company_city: config.remote_recipient.company_city ?? '',
            company_state: config.remote_recipient.company_state ?? '',
            company_zip: config.remote_recipient.company_zip ?? '',
            comp_receip_name: config.remote_recipient.comp_receip_name ?? '',
          }
        : {
            company_name: coName.trim(),
            company_email: coEmail.trim(),
            company_phone: coPhone.trim(),
            company_add_1: coAdd1.trim(),
            company_add_2: coAdd2.trim(),
            company_city: coCity.trim(),
            company_state: coState,
            company_zip: coZip.trim(),
            comp_receip_name: coRecipName.trim(),
          };

    const password = randomPassword();
    const body: Record<string, unknown> = {
      email: email.trim(),
      phone: phone.trim(),
      type_of_equipment: typeOfEquipment,
      order_type: orderType,
      employee_first_name: empFirst.trim(),
      employee_last_name: empLast.trim(),
      employee_email: empEmail.trim(),
      employee_phone: empPhone.trim(),
      employee_add_1: empAdd1.trim(),
      employee_add_2: empAdd2.trim(),
      employee_city: empCity.trim(),
      employee_state: empState,
      employee_zip: empZip.trim(),
      company_name: recipient.company_name,
      company_email: recipient.company_email,
      company_phone: recipient.company_phone,
      company_add_1: recipient.company_add_1,
      company_add_2: recipient.company_add_2,
      company_city: recipient.company_city,
      company_state: recipient.company_state,
      company_zip: recipient.company_zip,
      comp_receip_name: recipient.comp_receip_name,
      emp_custom_msg: empCustomMsg.trim() || undefined,
      password,
      user_pkg: 'basic',
      billing_amount: String(totalPay),
    };

    if (insTick) {
      body.ins_tick = '1';
      body.ins_amount = insAmount;
    }
    if (cpn) body.cpn = cpn;
    body.fcpn = fcpn;

    if (!paymentWaived) {
      body.billing_name = cardName.trim();
      body.billing_cc_no = onlyDigits(cardNo);
      body.billing_cc_expiry = cardExpiry.trim();
      body.billing_cc_cvv = onlyDigits(cardCvv);
    }

    setSubmitting(true);
    try {
      const { data } = await saasAxios.post<{
        status: string;
        message?: string;
        user?: { id: number; secret_code?: string };
      }>('/createorder', body, {
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      });
      if (data.status === 'success' && data.user?.id) {
        const uid = data.user.id;
        const sc = encodeURIComponent(String(data.user.secret_code ?? ''));
        window.location.assign(`/thank-you?uid=${uid}&secret_code=${sc}`);
        return;
      }
      setFormError(data.message ?? 'Order could not be placed.');
    } catch (err: unknown) {
      const ax = err as { response?: { data?: { message?: string } } };
      setFormError(ax.response?.data?.message ?? 'Order could not be placed.');
    } finally {
      setSubmitting(false);
    }
  }

  const stepLabels = ['Account & kit', 'Employee', 'Company & payment'];
  const activeSteps = 3;
  const visualStep = step === 0 ? 0 : step === 1 ? 1 : 2;

  if (configErr) {
    return (
      <div className="min-h-screen flex items-center justify-center p-6 bg-slate-50 dark:bg-slate-950">
        <p className="text-slate-600 dark:text-slate-400">{configErr}</p>
      </div>
    );
  }

  if (!config) {
    return (
      <div className="min-h-screen flex items-center justify-center p-6 bg-slate-50 dark:bg-slate-950 text-slate-500">
        Loading…
      </div>
    );
  }

  return (
    <div
      className="min-h-screen text-slate-900 dark:text-slate-100"
      style={{
        backgroundColor: uiDark ? '#000000' : 'var(--bg-page)',
        backgroundImage: 'none',
      }}
    >
      <header
        className="sticky top-0 z-10"
        style={{
          borderBottom: '1px solid var(--border-color)',
          backgroundColor: uiDark ? 'rgba(0, 0, 0, 0.72)' : '#ffffff',
          boxShadow: uiDark ? '0 10px 30px rgba(0, 0, 0, 0.45)' : 'none',
          opacity: 1,
          backdropFilter: uiDark ? 'blur(14px) saturate(140%)' : 'none',
          WebkitBackdropFilter: uiDark ? 'blur(14px) saturate(140%)' : 'none',
        }}
      >
        <div
          className="max-w-5xl mx-auto px-4 py-4 flex flex-wrap items-center justify-between gap-3"
          style={{ fontFamily: 'Inter, system-ui, sans-serif' }}
        >
          <div className="flex items-center gap-3">
            <div
              className="w-10 h-10 rounded-xl flex items-center justify-center"
              style={{ background: 'var(--brand-primary, #6366f1)', color: '#fff' }}
            >
              <Package className="w-5 h-5" />
            </div>
            <div>
              <div className="font-semibold text-lg">Return order</div>
              <div className="text-xs text-slate-500 dark:text-slate-400">Public order · same flow as the classic site</div>
            </div>
          </div>
          <div className="flex items-center gap-2">
            <button
              type="button"
              onClick={toggleAppearance}
              className="btn btn-outline btn-sm"
              aria-label={uiDark ? 'Light mode' : 'Dark mode'}
            >
              {uiDark ? 'Light' : 'Dark'}
            </button>
            {sessionState === 'guest' ? (
              <Link to="/login" className="btn btn-outline btn-sm">
                Log in
              </Link>
            ) : null}
            {sessionState === 'authed' ? (
              <Link to="/dashboard" className="btn btn-primary btn-sm">
                Dashboard
              </Link>
            ) : null}
          </div>
        </div>
      </header>

      <main className="max-w-5xl mx-auto px-4 py-10" style={{ fontFamily: 'Inter, system-ui, sans-serif' }}>
        <div className="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mb-6">
          <Truck className="w-4 h-4" />
          <span>
            Step {visualStep + 1} of {activeSteps} · {stepLabels[visualStep]}
          </span>
        </div>

        <div
          className="card reveal active"
          style={{
            padding: '28px 32px',
            background: uiDark
              ? 'linear-gradient(180deg, rgba(18,18,18,0.78) 0%, rgba(8,8,8,0.7) 100%)'
              : 'var(--surface-bg)',
            border: uiDark
              ? '1px solid rgba(255, 255, 255, 0.12)'
              : '1px solid var(--border-color)',
            boxShadow: uiDark
              ? '0 10px 32px rgba(0, 0, 0, 0.45)'
              : undefined,
            backdropFilter: uiDark ? 'blur(10px)' : 'none',
            WebkitBackdropFilter: uiDark ? 'blur(10px)' : 'none',
          }}
        >
          {formError ? (
            <div className="form-group" style={{ color: 'crimson', fontSize: 14, marginBottom: 16 }}>
              {formError}
            </div>
          ) : null}

          {step === 0 ? (
            <div>
              <h1 className="page-title" style={{ fontSize: '1.5rem', marginBottom: 8 }}>
                Start a device return
              </h1>
              <p style={{ color: 'var(--text-muted)', marginBottom: 28, fontSize: 14 }}>
                We ship a prepaid box to your employee. Complete the steps to place your order.
              </p>
              <div className="form-group">
                <label className="form-label" htmlFor="po-email">
                  Your email
                </label>
                <input
                  id="po-email"
                  className="form-input"
                  type="email"
                  autoComplete="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  readOnly={lockAutofilledEmail}
                  required
                />
              </div>
              <div className="form-group">
                <label className="form-label" htmlFor="po-phone">
                  Your phone
                </label>
                <input
                  id="po-phone"
                  className="form-input"
                  type="tel"
                  autoComplete="tel"
                  value={phone}
                  onChange={(e) => setPhone(e.target.value)}
                  readOnly={lockAutofilledPhone}
                  required
                />
                {accountMissingPhoneHint ? (
                  <p style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 6, marginBottom: 0 }}>
                    No phone is saved on your account—enter one here so we can reach you about this return.
                  </p>
                ) : null}
              </div>
              <div className="form-group">
                <label className="form-label" htmlFor="po-equip">
                  Equipment
                </label>
                <ThemedSelect
                  value={typeOfEquipment}
                  onChange={setTypeOfEquipment}
                  uiDark={uiDark}
                  placeholder="Select equipment"
                  options={[
                    { value: 'Laptop', label: 'Laptop' },
                    { value: 'Monitor', label: 'Monitor' },
                  ]}
                />
              </div>
              <div className="form-group">
                <label className="form-label" htmlFor="po-ordertype">
                  Return service
                </label>
                <ThemedSelect
                  value={orderType}
                  onChange={(v) => setOrderType(v as 'Return To Company' | 'Sell This Equipment')}
                  uiDark={uiDark}
                  placeholder="Select return service"
                  options={[
                    { value: 'Return To Company', label: 'Return to company' },
                    { value: 'Sell This Equipment', label: 'Recycle with data destruction' },
                  ]}
                />
              </div>
              <div className="form-group">
                <label
                  className="form-label"
                  style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: 10,
                    cursor: 'pointer',
                    marginBottom: 0,
                    lineHeight: 1.3,
                  }}
                >
                  <input
                    type="checkbox"
                    checked={insTick}
                    onChange={(e) => setInsTick(e.target.checked)}
                    style={{
                      width: 18,
                      height: 18,
                      margin: 0,
                      appearance: 'none',
                      WebkitAppearance: 'none',
                      border: `1px solid ${insTick ? 'var(--brand-primary, #6366f1)' : 'var(--border-color, #cbd5e1)'}`,
                      borderRadius: 4,
                      backgroundColor: insTick ? 'var(--brand-primary, #6366f1)' : 'var(--input-bg, #fff)',
                      backgroundRepeat: 'no-repeat',
                      backgroundPosition: 'center',
                      backgroundSize: '12px 12px',
                      backgroundImage: insTick
                        ? `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='white' d='M6.2 11.2 2.9 8l1.1-1.1 2.2 2.2 5-5L12.3 5z'/%3E%3C/svg%3E")`
                        : 'none',
                      cursor: 'pointer',
                      flex: '0 0 auto',
                    }}
                  />
                  Insure this shipment (declared value)
                </label>
                {insTick ? (
                  <input
                    className="form-input"
                    style={{ marginTop: 8 }}
                    type="number"
                    min={1}
                    step="1"
                    placeholder="Declared value (USD)"
                    value={insAmount}
                    onChange={(e) => setInsAmount(e.target.value)}
                  />
                ) : null}
              </div>
              <div style={{ marginTop: 24, display: 'flex', justifyContent: 'flex-end' }}>
                <button type="button" className="btn btn-primary" onClick={nextFromAccount}>
                  Continue
                </button>
              </div>
            </div>
          ) : null}

          {step === 1 ? (
            <div>
              <h2 className="card-title" style={{ marginBottom: 20 }}>
                Employee receiving the box
              </h2>
              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: 16 }}>
                <div className="form-group">
                  <label className="form-label">First name</label>
                  <input className="form-input" value={empFirst} onChange={(e) => setEmpFirst(e.target.value)} required />
                </div>
                <div className="form-group">
                  <label className="form-label">Last name</label>
                  <input className="form-input" value={empLast} onChange={(e) => setEmpLast(e.target.value)} required />
                </div>
                <div className="form-group">
                  <label className="form-label">Email</label>
                  <input className="form-input" type="email" value={empEmail} onChange={(e) => setEmpEmail(e.target.value)} required />
                </div>
                <div className="form-group">
                  <label className="form-label">Phone</label>
                  <input className="form-input" type="tel" value={empPhone} onChange={(e) => setEmpPhone(e.target.value)} required />
                </div>
                <div className="form-group" style={{ gridColumn: '1 / -1' }}>
                  <label className="form-label">Address line 1</label>
                  <input className="form-input" value={empAdd1} onChange={(e) => setEmpAdd1(e.target.value)} required />
                </div>
                <div className="form-group" style={{ gridColumn: '1 / -1' }}>
                  <label className="form-label">Address line 2</label>
                  <input className="form-input" value={empAdd2} onChange={(e) => setEmpAdd2(e.target.value)} />
                </div>
                <div className="form-group">
                  <label className="form-label">City</label>
                  <input className="form-input" value={empCity} onChange={(e) => setEmpCity(e.target.value)} required />
                </div>
                <div className="form-group">
                  <label className="form-label">State</label>
                  <StateSelect id="emp-st" value={empState} onChange={setEmpState} required uiDark={uiDark} />
                </div>
                <div className="form-group">
                  <label className="form-label">ZIP</label>
                  <input className="form-input" value={empZip} onChange={(e) => setEmpZip(e.target.value)} required />
                </div>
                <div className="form-group" style={{ gridColumn: '1 / -1' }}>
                  <label className="form-label">Message to employee (optional)</label>
                  <input className="form-input" value={empCustomMsg} onChange={(e) => setEmpCustomMsg(e.target.value)} />
                </div>
              </div>
              <div style={{ marginTop: 28, display: 'flex', justifyContent: 'space-between' }}>
                <button type="button" className="btn btn-outline" onClick={back}>
                  <ArrowLeft className="w-4 h-4" style={{ display: 'inline', verticalAlign: 'middle' }} /> Back
                </button>
                <button type="button" className="btn btn-primary" onClick={nextFromEmployee}>
                  Continue
                </button>
              </div>
            </div>
          ) : null}

          {step === 3 ? (
            <form onSubmit={submitOrder}>
              <h2 className="card-title" style={{ marginBottom: 20 }}>
                {orderType === 'Return To Company' ? 'Return address & payment' : 'Recycle destination & payment'}
              </h2>
              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: 16 }}>
                <div className="form-group">
                  <label className="form-label">Company name</label>
                  <input
                    className="form-input"
                    value={coName}
                    onChange={(e) => setCoName(e.target.value)}
                    required
                    readOnly={lockCompanyDestination}
                  />
                </div>
                <div className="form-group">
                  <label className="form-label">Recipient name</label>
                  <input
                    className="form-input"
                    value={coRecipName}
                    onChange={(e) => setCoRecipName(e.target.value)}
                    required
                    readOnly={lockCompanyDestination}
                  />
                </div>
                <div className="form-group">
                  <label className="form-label">Company email</label>
                  <input
                    className="form-input"
                    type="email"
                    value={coEmail}
                    onChange={(e) => setCoEmail(e.target.value)}
                    required
                    readOnly={lockCompanyDestination}
                  />
                </div>
                <div className="form-group">
                  <label className="form-label">Company phone</label>
                  <input
                    className="form-input"
                    type="tel"
                    value={coPhone}
                    onChange={(e) => setCoPhone(e.target.value)}
                    required
                    readOnly={lockCompanyDestination}
                  />
                </div>
                <div className="form-group" style={{ gridColumn: '1 / -1' }}>
                  <label className="form-label">Address line 1</label>
                  <input
                    className="form-input"
                    value={coAdd1}
                    onChange={(e) => setCoAdd1(e.target.value)}
                    required
                    readOnly={lockCompanyDestination}
                  />
                </div>
                <div className="form-group" style={{ gridColumn: '1 / -1' }}>
                  <label className="form-label">Address line 2</label>
                  <input
                    className="form-input"
                    value={coAdd2}
                    onChange={(e) => setCoAdd2(e.target.value)}
                    readOnly={lockCompanyDestination}
                  />
                </div>
                <div className="form-group">
                  <label className="form-label">City</label>
                  <input
                    className="form-input"
                    value={coCity}
                    onChange={(e) => setCoCity(e.target.value)}
                    required
                    readOnly={lockCompanyDestination}
                  />
                </div>
                <div className="form-group">
                  <label className="form-label">State</label>
                  <StateSelect
                    id="co-st"
                    value={coState}
                    onChange={setCoState}
                    required
                    uiDark={uiDark}
                    disabled={lockCompanyDestination}
                  />
                </div>
                <div className="form-group">
                  <label className="form-label">ZIP</label>
                  <input
                    className="form-input"
                    value={coZip}
                    onChange={(e) => setCoZip(e.target.value)}
                    required
                    readOnly={lockCompanyDestination}
                  />
                </div>
              </div>

              <div
                style={{
                  marginTop: 28,
                  paddingTop: 24,
                  borderTop: '1px solid var(--border-color)',
                  display: 'grid',
                  gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))',
                  gap: 20,
                }}
              >
                <div>
                  <div className="form-label" style={{ marginBottom: 8 }}>
                    Coupon
                  </div>
                  <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
                    <input
                      className="form-input"
                      style={{ flex: '1 1 160px', minWidth: 0 }}
                      value={couponDraft}
                      onChange={(e) => setCouponDraft(e.target.value)}
                      placeholder="Code"
                    />
                    <button type="button" className="btn btn-outline" onClick={() => void applyCoupon()}>
                      Apply
                    </button>
                  </div>
                  {couponMsg ? (
                    <p style={{ fontSize: 13, marginTop: 8, color: 'var(--text-muted)' }}>{couponMsg}</p>
                  ) : null}
                </div>
                <div>
                  <div className="form-label" style={{ marginBottom: 8 }}>
                    Order total
                  </div>
                  <div style={{ fontSize: 28, fontWeight: 700 }}>
                    ${totalPay.toFixed(2)}
                  </div>
                  <p style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 4 }}>
                    Device ${deviceAmt.toFixed(2)}
                    {insComputed > 0 ? ` · Insurance est. $${insComputed.toFixed(2)}` : ''}
                  </p>
                </div>
              </div>

              {!paymentWaived ? (
                <div style={{ marginTop: 24 }}>
                  <h3 className="form-label" style={{ marginBottom: 12 }}>
                    Payment
                  </h3>
                  <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: 16 }}>
                    <div className="form-group" style={{ gridColumn: '1 / -1' }}>
                      <label className="form-label">Name on card</label>
                      <input
                        className="form-input"
                        value={cardName}
                        onChange={(e) => setCardName(sanitizeCardholderName(e.target.value))}
                        maxLength={60}
                        placeholder="Full name on card"
                      />
                    </div>
                    <div className="form-group" style={{ gridColumn: '1 / -1' }}>
                      <label className="form-label">Card number</label>
                      <input
                        className="form-input"
                        value={cardNo}
                        onChange={(e) => setCardNo(formatCardNumber(e.target.value))}
                        inputMode="numeric"
                        maxLength={19}
                        placeholder="Card number"
                      />
                    </div>
                    <div className="form-group">
                      <label className="form-label">Expires (YYYY-MM)</label>
                      <input
                        className="form-input"
                        value={cardExpiry}
                        onChange={(e) => setCardExpiry(formatExpiryYearMonth(e.target.value))}
                        placeholder="2026-04"
                        pattern="\d{4}-(0[1-9]|1[0-2])"
                        maxLength={7}
                      />
                    </div>
                    <div className="form-group">
                      <label className="form-label">CVV</label>
                      <input
                        className="form-input"
                        value={cardCvv}
                        onChange={(e) => setCardCvv(onlyDigits(e.target.value).slice(0, 4))}
                        inputMode="numeric"
                        maxLength={4}
                        placeholder="123"
                      />
                    </div>
                  </div>
                </div>
              ) : (
                <p style={{ marginTop: 20, fontSize: 14, color: 'var(--text-muted)' }}>
                  No card required for this order (tenant defaults or 100% coupon).
                </p>
              )}

              <div style={{ marginTop: 32, display: 'flex', justifyContent: 'space-between', flexWrap: 'wrap', gap: 12 }}>
                <button type="button" className="btn btn-outline" onClick={back}>
                  Back
                </button>
                <button type="submit" className="btn btn-primary" disabled={submitting}>
                  {submitting ? 'Submitting…' : fcpn === 1 ? 'Place order' : 'Pay & place order'}
                </button>
              </div>
            </form>
          ) : null}
        </div>
      </main>
    </div>
  );
}
