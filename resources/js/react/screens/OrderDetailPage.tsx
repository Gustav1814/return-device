import { useEffect, useMemo, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { ArrowLeft, Printer } from 'lucide-react';
import { saasAxios } from '../api/saasAxios';
import { userInitial } from '../utils/userInitial';

function statusPillClass(status: string) {
  const u = status.toUpperCase();
  if (u === 'DELIVERED') return 'status-pill status-completed';
  if (u.includes('PENDING') || u.includes('LABEL')) return 'status-pill status-pending';
  return 'status-pill status-progress';
}

function statusLabel(receive?: string | null, orderStatus?: string | null) {
  const r = (receive ?? '').toUpperCase();
  if (r === 'DELIVERED') return 'Delivered';
  if (r && r !== '') return receive ?? 'In Transit';
  return orderStatus ?? 'In Transit';
}

type LabelType = 'emp' | 'rec' | 'dest';
type LabelRate = {
  object_id?: string;
  objectId?: string;
  id?: string;
  rate_object_id?: string;
  amount?: string | number;
  currency?: string;
  provider?: string;
  carrier?: string;
  carrier_name?: string;
  provider_image_75?: string;
  estimated_days?: number | string;
  servicelevel?: { token?: string; name?: string };
  service?: string;
};
type PurchasedLabel = {
  label_url?: string;
  tracking_number?: string;
  tracking_url_provider?: string;
  object_id?: string;
};

function parseLabelResponse(raw: unknown): PurchasedLabel | null {
  if (!raw) return null;
  if (typeof raw === 'string') {
    try {
      return JSON.parse(raw) as PurchasedLabel;
    } catch {
      return null;
    }
  }
  if (typeof raw === 'object') return raw as PurchasedLabel;
  return null;
}

/** DB / API sometimes stores 0, "0", or empty for "no tracking status yet". */
function isPlaceholderTimelineValue(raw: unknown): boolean {
  if (raw === null || raw === undefined || raw === false) return true;
  if (typeof raw === 'number' && raw === 0) return true;
  if (typeof raw === 'string') {
    const s = raw.trim().toLowerCase();
    if (s === '' || s === '0' || s === 'null') return true;
  }
  return false;
}

function hasPurchasedLabelDetail(parsed: PurchasedLabel | null): boolean {
  return Boolean(String(parsed?.label_url ?? '').trim() || String(parsed?.tracking_number ?? '').trim());
}

function hasShippingLabelTimelineDetail(raw: unknown, parsed: PurchasedLabel | null): boolean {
  if (hasPurchasedLabelDetail(parsed)) return true;
  return !isPlaceholderTimelineValue(raw);
}

function hasReceiveTimelineDetail(raw: unknown, parsed: PurchasedLabel | null): boolean {
  if (hasPurchasedLabelDetail(parsed)) return true;
  return !isPlaceholderTimelineValue(raw);
}

/** Shippo-style statuses: PRE_TRANSIT → Pre transit */
function formatShippoTrackingStatus(raw: string): string {
  const u = raw.trim();
  if (!u) return '';
  return u
    .split('_')
    .filter(Boolean)
    .map((w) => w.charAt(0) + w.slice(1).toLowerCase())
    .join(' ');
}

function orderCreatedSummaryLine(row: Record<string, unknown>): string {
  const orderRef = row.order_id != null ? `#${row.order_id}` : `#${row.id ?? ''}`;
  const emp = [row.emp_first_name, row.emp_last_name].filter(Boolean).join(' ').trim();
  const company = String(row.company_name ?? '').trim();
  const equip = String(row.type_of_equip ?? '').trim();
  const parts: string[] = [`Order ${orderRef}`];
  if (emp) parts.push(emp);
  if (company) parts.push(company);
  if (equip) parts.push(equip);
  return `${parts.join(' · ')} submitted.`;
}

/** First row title: never imply a shipping label exists before purchase. */
function firstTimelineTitle(parsed: PurchasedLabel | null, sendLabelStatus: unknown): string {
  if (hasPurchasedLabelDetail(parsed)) return 'Box label created';
  if (!isPlaceholderTimelineValue(sendLabelStatus))
    return `Box label · ${formatShippoTrackingStatus(String(sendLabelStatus))}`;
  return 'Label pending';
}

function firstTimelineHint(parsed: PurchasedLabel | null, sendLabelStatus: unknown): string | null {
  if (hasPurchasedLabelDetail(parsed)) return null;
  if (!isPlaceholderTimelineValue(sendLabelStatus)) return null;
  return 'No box label has been purchased yet. Use Create Box Label when ready.';
}

function shippingTimelineSubtitle(
  sendLabelStatus: unknown,
  parsed: PurchasedLabel | null,
): string {
  if (String(parsed?.tracking_number ?? '').trim()) return `Tracking: ${parsed!.tracking_number}`;
  if (String(parsed?.label_url ?? '').trim()) return 'Box label purchased — open link below.';
  if (!isPlaceholderTimelineValue(sendLabelStatus)) return formatShippoTrackingStatus(String(sendLabelStatus));
  return '';
}

function receiveTimelineSubtitle(receiveStatus: unknown, parsed: PurchasedLabel | null): string {
  if (String(parsed?.tracking_number ?? '').trim()) return `Tracking: ${parsed!.tracking_number}`;
  if (String(parsed?.label_url ?? '').trim()) return 'Return label purchased — open link below.';
  if (!isPlaceholderTimelineValue(receiveStatus)) return formatShippoTrackingStatus(String(receiveStatus));
  return '';
}

function normalizeRates(raw: unknown): LabelRate[] {
  if (Array.isArray(raw)) return raw as LabelRate[];
  if (raw && typeof raw === 'object') {
    return Object.values(raw as Record<string, LabelRate>);
  }
  return [];
}

function readRateObjectId(rate: LabelRate): string {
  return String(rate.object_id ?? rate.objectId ?? rate.rate_object_id ?? rate.id ?? '').trim();
}

function readRateProvider(rate: LabelRate): string {
  return String(rate.provider ?? rate.carrier ?? rate.carrier_name ?? '').trim();
}

function readRateService(rate: LabelRate): string {
  return String(rate.servicelevel?.token ?? rate.servicelevel?.name ?? rate.service ?? '').trim();
}

function readRateAmount(rate: LabelRate): string {
  const amount = rate.amount ?? '';
  const currency = rate.currency ?? '';
  if (amount === '' && !currency) return '—';
  return `${String(amount)}${String(currency)}`.trim();
}

function readRateDays(rate: LabelRate): string {
  const raw = rate.estimated_days;
  if (raw === null || raw === undefined || String(raw).trim() === '') return '—';
  return String(raw);
}

function extractLabelError(raw: unknown): string {
  if (!raw) return 'Could not load label rates.';
  if (typeof raw === 'string') return raw;
  if (typeof raw === 'object') {
    const d = raw as Record<string, unknown>;
    if (typeof d.message === 'string' && d.message.trim()) return d.message;
    if (typeof d.error === 'string' && d.error.trim()) return d.error;
    // Shippo messages array: [{source, code, text}, ...]
    if (Array.isArray(d.messages) && d.messages.length > 0) {
      const first = d.messages[0];
      if (first && typeof first === 'object' && typeof (first as Record<string, unknown>).text === 'string') {
        return (first as Record<string, unknown>).text as string;
      }
    }
    if (typeof d.status === 'string' && d.status.trim() && d.status !== 'SUCCESS') return d.status;
    if (Array.isArray(d.errors) && d.errors.length > 0) return String(d.errors[0]);
    if (Array.isArray(d.rate) && d.rate.length > 0) return String(d.rate[0]);
  }
  return 'Could not load label rates.';
}

export function OrderDetailPage() {
  const { itemId } = useParams();
  const [data, setData] = useState<any>(null);
  const [rateLoading, setRateLoading] = useState<Record<LabelType, boolean>>({
    emp: false,
    rec: false,
    dest: false,
  });
  /** Only the clicked rate shows "Purchasing…" (matches classic dashboard per-button spinner). */
  const [purchasingRateObjectId, setPurchasingRateObjectId] = useState<string | null>(null);
  const [rateError, setRateError] = useState<Record<LabelType, string | null>>({
    emp: null,
    rec: null,
    dest: null,
  });
  const [rates, setRates] = useState<Record<LabelType, LabelRate[]>>({
    emp: [],
    rec: [],
    dest: [],
  });

  async function reloadOrder() {
    if (!itemId) return;
    const r = await saasAxios.get(`/api/saas/orders/${itemId}`);
    setData(r.data);
  }

  useEffect(() => {
    reloadOrder().catch(() => setData(null));
  }, [itemId]);

  const orderTitle = useMemo(() => {
    if (!data) return 'Order';
    return data.order_id ? `#ORD-${data.order_id}` : `#${data.id}`;
  }, [data]);

  const serialFromCustom = useMemo(() => {
    const msg = String(data?.custom_msg ?? '');
    const m = msg.match(/Serial:\s*(.+)/i);
    return m ? m[1].trim() : '—';
  }, [data]);

  if (!data) {
    return (
      <div className="content-wrapper">
        <div className="page-header reveal active">
          <h1 className="page-title">Order detail</h1>
        </div>
        <div className="card reveal active">Loading…</div>
      </div>
    );
  }

  const recv = data.receive_label_status ?? '';
  const pillText = statusLabel(data.receive_label_status, data.order_status);
  const orderStatus = String(data.order_status ?? '').toLowerCase();
  const canMarkComplete = orderStatus !== 'completed';
  const showDestinationLabel = Boolean(data.return_additional_srv);
  const sendLabel = parseLabelResponse(data.send_labelresponse);
  const receiveLabel = parseLabelResponse(data.receive_labelresponse);
  const destLabel = parseLabelResponse(data.dest_labelresponse);
  const timelineFirstHint = firstTimelineHint(sendLabel, data.send_label_status);

  async function loadRates(type: LabelType) {
    if (!data?.id) return;
    setRateLoading((s) => ({ ...s, [type]: true }));
    setRateError((s) => ({ ...s, [type]: null }));
    try {
      const { data: res } = await saasAxios.get('/createlabel', {
        params: { oid: data.id, t: type },
      });

      // #region agent log
      {
        const rawRates = res?.rates ?? res?.response?.rates;
        const normalized = normalizeRates(rawRates);
        const first = normalized[0];
        fetch('http://127.0.0.1:7301/ingest/9e837ecd-6a31-4959-b615-c92393ed8d28', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-Debug-Session-Id': 'aa8310' },
          body: JSON.stringify({
            sessionId: 'aa8310',
            hypothesisId: 'H1-H5',
            location: 'OrderDetailPage.tsx:loadRates',
            message: 'createlabel client snapshot',
            data: {
              labelType: type,
              orderRowId: data.id,
              resStatus: res?.status,
              rawRatesIsArray: Array.isArray(rawRates),
              rawRatesCountable:
                rawRates && typeof rawRates === 'object'
                  ? Object.keys(rawRates as object).length
                  : null,
              normalizedLen: normalized.length,
              firstRateKeys: first ? Object.keys(first).slice(0, 25) : [],
              firstReadObjectId: first ? readRateObjectId(first) : '',
              filteredLen: normalized.filter((r) => readRateObjectId(r) !== '').length,
              sendLabelStatus: data.send_label_status,
              sendLabelStatusType: typeof data.send_label_status,
            },
            timestamp: Date.now(),
            runId: 'pre-fix',
          }),
        }).catch(() => {});
      }
      // #endregion

      if (res?.status === 'SUCCESS') {
        const nextRates = normalizeRates(res?.rates ?? res?.response?.rates).filter(
          (rate) => readRateObjectId(rate) !== '',
        );
        setRates((s) => ({ ...s, [type]: nextRates }));
        if (nextRates.length === 0) {
          setRateError((s) => ({ ...s, [type]: 'No shipping rates returned for this order.' }));
        }
      } else {
        setRates((s) => ({ ...s, [type]: [] }));
        setRateError((s) => ({ ...s, [type]: extractLabelError(res?.response) }));
      }
    } catch (err: unknown) {
      const ax = err as { response?: { data?: unknown } };
      setRates((s) => ({ ...s, [type]: [] }));
      setRateError((s) => ({ ...s, [type]: extractLabelError(ax.response?.data) }));
    } finally {
      setRateLoading((s) => ({ ...s, [type]: false }));
    }
  }

  async function purchaseLabel(type: LabelType, rateObjectId: string) {
    if (!data?.id) return;
    setPurchasingRateObjectId(rateObjectId);
    setRateError((s) => ({ ...s, [type]: null }));
    try {
      const { data: res } = await saasAxios.get('/purchaselabel', {
        params: { oid: rateObjectId, t: type, suborder: data.id },
      });
      if (res?.status === 'SUCCESS') {
        setRates((s) => ({ ...s, [type]: [] }));
        await reloadOrder();
      } else {
        setRateError((s) => ({ ...s, [type]: extractLabelError(res?.response) }));
      }
    } catch (err: unknown) {
      const ax = err as { response?: { data?: unknown } };
      setRateError((s) => ({ ...s, [type]: extractLabelError(ax.response?.data) }));
    } finally {
      setPurchasingRateObjectId(null);
    }
  }

  return (
    <div className="content-wrapper">
      <div className="page-header reveal active">
        <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
          <Link to="/orders/in-progress" className="icon-btn" aria-label="Back to orders">
            <ArrowLeft size={18} />
          </Link>
          <h1 className="page-title">Order {orderTitle}</h1>
          <span className={statusPillClass(recv || String(data.order_status ?? ''))}>{pillText}</span>
        </div>
        <div className="toolbar">
          <button type="button" className="btn btn-outline" onClick={() => window.print()}>
            <Printer size={18} /> Print Label
          </button>
          {canMarkComplete ? (
            <Link
              className="top-bar-page-chip top-bar-page-chip--link"
              to={`/orders/${data.id}/payment`}
            >
              <span className="top-bar-page-chip__shine" aria-hidden />
              <span className="top-bar-page-chip__text">Mark as Completed</span>
            </Link>
          ) : (
            <button
              type="button"
              className="top-bar-page-chip top-bar-page-chip--btn"
              disabled
              title="Not available via API yet"
            >
              <span className="top-bar-page-chip__shine" aria-hidden />
              <span className="top-bar-page-chip__text">Mark as Completed</span>
            </button>
          )}
        </div>
      </div>

      <div className="dashboard-row">
        <div className="card reveal active">
          <h3 className="card-title" style={{ marginBottom: 24 }}>
            Shipment Timeline
          </h3>
          <div className="timeline" style={{ position: 'relative', paddingLeft: 32 }}>
            <div
              style={{
                position: 'absolute',
                left: 7,
                top: 0,
                bottom: 0,
                width: 2,
                background: 'var(--border-color)',
              }}
            />

            <div style={{ position: 'relative', marginBottom: 32 }}>
              <div
                style={{
                  position: 'absolute',
                  left: -32,
                  top: 0,
                  width: 16,
                  height: 16,
                  borderRadius: '50%',
                  background: 'var(--brand-primary)',
                  border: '4px solid var(--bg-card)',
                }}
              />
              <div style={{ fontWeight: 600 }}>{firstTimelineTitle(sendLabel, data.send_label_status)}</div>
              <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>
                {data.created_at
                  ? String(data.created_at).replace('T', ' ').slice(0, 16)
                  : '—'}
              </div>
              <p style={{ fontSize: 14, marginTop: 4 }}>{orderCreatedSummaryLine(data as Record<string, unknown>)}</p>
              {timelineFirstHint ? (
                <p style={{ fontSize: 13, color: 'var(--text-muted)', marginTop: 6, marginBottom: 0 }}>
                  {timelineFirstHint}
                </p>
              ) : null}
            </div>

            {hasShippingLabelTimelineDetail(data.send_label_status, sendLabel) ? (
              <div style={{ position: 'relative', marginBottom: 32 }}>
                <div
                  style={{
                    position: 'absolute',
                    left: -32,
                    top: 0,
                    width: 16,
                    height: 16,
                    borderRadius: '50%',
                    background: 'var(--brand-primary)',
                    border: '4px solid var(--bg-card)',
                  }}
                />
                <div style={{ fontWeight: 600 }}>Box shipment (to employee)</div>
                <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>
                  {shippingTimelineSubtitle(data.send_label_status, sendLabel) || '—'}
                </div>
              </div>
            ) : null}

            {hasReceiveTimelineDetail(data.receive_label_status, receiveLabel) ? (
              <div style={{ position: 'relative' }}>
                <div
                  style={{
                    position: 'absolute',
                    left: -32,
                    top: 0,
                    width: 16,
                    height: 16,
                    borderRadius: '50%',
                    background: 'var(--brand-primary)',
                    border: '4px solid var(--bg-card)',
                  }}
                />
                <div style={{ fontWeight: 600 }}>Return shipment (to company)</div>
                <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>
                  {receiveTimelineSubtitle(data.receive_label_status, receiveLabel) || '—'}
                </div>
              </div>
            ) : null}
          </div>
        </div>

        <div className="card reveal active">
          <h3 className="card-title" style={{ marginBottom: 24 }}>
            Customer Info
          </h3>
          <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 20 }}>
            <span
              className="user-initial-avatar"
              style={{ width: 48, height: 48, fontSize: 18 }}
              aria-hidden
            >
              {userInitial(data.receipient_name, data.receipient_email)}
            </span>
            <div>
              <div style={{ fontWeight: 600 }}>{data.receipient_name ?? '—'}</div>
              <div style={{ fontSize: 14, color: 'var(--text-muted)' }}>
                {data.receipient_email ?? '—'}
              </div>
            </div>
          </div>
          <div style={{ fontSize: 14 }}>
            <div style={{ marginBottom: 12 }}>
              <div
                style={{
                  color: 'var(--text-muted)',
                  fontSize: 12,
                  textTransform: 'uppercase',
                }}
              >
                Equipment
              </div>
              <div>{data.type_of_equip ?? '—'}</div>
            </div>
            <div style={{ marginBottom: 12 }}>
              <div
                style={{
                  color: 'var(--text-muted)',
                  fontSize: 12,
                  textTransform: 'uppercase',
                }}
              >
                Serial Number
              </div>
              <div style={{ fontFamily: 'monospace' }}>{serialFromCustom}</div>
            </div>
            <div>
              <div
                style={{
                  color: 'var(--text-muted)',
                  fontSize: 12,
                  textTransform: 'uppercase',
                }}
              >
                Return Address
              </div>
              <div>
                {data.receipient_add_1 ? (
                  <>
                    {data.receipient_add_1}
                    <br />
                    {[data.receipient_city, data.receipient_state, data.receipient_zip]
                      .filter(Boolean)
                      .join(', ')}
                  </>
                ) : (
                  '—'
                )}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="dashboard-row">
        <div className="card reveal active">
          <h3 className="card-title" style={{ marginBottom: 16 }}>
            Box Label
          </h3>
          {data.send_flag === 1 && sendLabel?.label_url ? (
            <div style={{ fontSize: 14 }}>
              <div>
                <a href={sendLabel.label_url} target="_blank" rel="noreferrer">
                  Open label
                </a>
              </div>
              <div style={{ marginTop: 6 }}>Tracking: {sendLabel.tracking_number ?? '—'}</div>
            </div>
          ) : (
            <>
              <button className="btn btn-outline" type="button" onClick={() => loadRates('emp')} disabled={rateLoading.emp}>
                {rateLoading.emp ? 'Loading rates…' : 'Create Box Label'}
              </button>
              {rateError.emp ? <p style={{ color: '#ef4444', marginTop: 10 }}>{rateError.emp}</p> : null}
              {rates.emp.length > 0 ? (
                <div style={{ marginTop: 14, display: 'grid', gap: 10 }}>
                  {rates.emp.map((r) => {
                    const rateOid = readRateObjectId(r);
                    const isBuying = purchasingRateObjectId === rateOid;
                    return (
                      <div key={rateOid} style={{ border: '1px solid var(--border-color)', borderRadius: 10, padding: 10 }}>
                        <div style={{ fontWeight: 600 }}>
                          {readRateProvider(r) || 'Carrier'} ({readRateService(r) || 'service'})
                        </div>
                        <div style={{ fontSize: 13, color: 'var(--text-muted)' }}>
                          {readRateAmount(r)} · Est. {readRateDays(r)} days
                        </div>
                        <button
                          className="btn btn-primary"
                          style={{ marginTop: 8 }}
                          type="button"
                          disabled={isBuying}
                          onClick={() => purchaseLabel('emp', rateOid)}
                        >
                          {isBuying ? 'Purchasing…' : 'Purchase Label'}
                        </button>
                      </div>
                    );
                  })}
                </div>
              ) : null}
            </>
          )}
        </div>

        <div className="card reveal active">
          <h3 className="card-title" style={{ marginBottom: 16 }}>
            Device Label
          </h3>
          {data.rec_flag === 1 && receiveLabel?.label_url ? (
            <div style={{ fontSize: 14 }}>
              <div>
                <a href={receiveLabel.label_url} target="_blank" rel="noreferrer">
                  Open label
                </a>
              </div>
              <div style={{ marginTop: 6 }}>Tracking: {receiveLabel.tracking_number ?? '—'}</div>
            </div>
          ) : (
            <>
              <button className="btn btn-outline" type="button" onClick={() => loadRates('rec')} disabled={rateLoading.rec}>
                {rateLoading.rec ? 'Loading rates…' : 'Create Device Label'}
              </button>
              {rateError.rec ? <p style={{ color: '#ef4444', marginTop: 10 }}>{rateError.rec}</p> : null}
              {rates.rec.length > 0 ? (
                <div style={{ marginTop: 14, display: 'grid', gap: 10 }}>
                  {rates.rec.map((r) => {
                    const rateOid = readRateObjectId(r);
                    const isBuying = purchasingRateObjectId === rateOid;
                    return (
                      <div key={rateOid} style={{ border: '1px solid var(--border-color)', borderRadius: 10, padding: 10 }}>
                        <div style={{ fontWeight: 600 }}>
                          {readRateProvider(r) || 'Carrier'} ({readRateService(r) || 'service'})
                        </div>
                        <div style={{ fontSize: 13, color: 'var(--text-muted)' }}>
                          {readRateAmount(r)} · Est. {readRateDays(r)} days
                        </div>
                        <button
                          className="btn btn-primary"
                          style={{ marginTop: 8 }}
                          type="button"
                          disabled={isBuying}
                          onClick={() => purchaseLabel('rec', rateOid)}
                        >
                          {isBuying ? 'Purchasing…' : 'Purchase Label'}
                        </button>
                      </div>
                    );
                  })}
                </div>
              ) : null}
            </>
          )}
        </div>
      </div>

      {showDestinationLabel ? (
        <div className="dashboard-row">
          <div className="card reveal active">
            <h3 className="card-title" style={{ marginBottom: 16 }}>
              Destination Label
            </h3>
            {data.dest_flag === 1 && destLabel?.label_url ? (
              <div style={{ fontSize: 14 }}>
                <div>
                  <a href={destLabel.label_url} target="_blank" rel="noreferrer">
                    Open label
                  </a>
                </div>
                <div style={{ marginTop: 6 }}>Tracking: {destLabel.tracking_number ?? '—'}</div>
              </div>
            ) : (
              <>
                <button className="btn btn-outline" type="button" onClick={() => loadRates('dest')} disabled={rateLoading.dest}>
                  {rateLoading.dest ? 'Loading rates…' : 'Create Destination Label'}
                </button>
                {rateError.dest ? <p style={{ color: '#ef4444', marginTop: 10 }}>{rateError.dest}</p> : null}
                {rates.dest.length > 0 ? (
                  <div style={{ marginTop: 14, display: 'grid', gap: 10 }}>
                    {rates.dest.map((r) => {
                      const rateOid = readRateObjectId(r);
                      const isBuying = purchasingRateObjectId === rateOid;
                      return (
                        <div key={rateOid} style={{ border: '1px solid var(--border-color)', borderRadius: 10, padding: 10 }}>
                          <div style={{ fontWeight: 600 }}>
                            {readRateProvider(r) || 'Carrier'} ({readRateService(r) || 'service'})
                          </div>
                          <div style={{ fontSize: 13, color: 'var(--text-muted)' }}>
                            {readRateAmount(r)} · Est. {readRateDays(r)} days
                          </div>
                          <button
                            className="btn btn-primary"
                            style={{ marginTop: 8 }}
                            type="button"
                            disabled={isBuying}
                            onClick={() => purchaseLabel('dest', rateOid)}
                          >
                            {isBuying ? 'Purchasing…' : 'Purchase Label'}
                          </button>
                        </div>
                      );
                    })}
                  </div>
                ) : null}
              </>
            )}
          </div>
        </div>
      ) : null}
    </div>
  );
}
