import { useCallback, useRef, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import {
  ArrowLeft,
  Download,
  FileText,
  Info,
  Loader2,
  Upload,
  X,
} from 'lucide-react';
import { saasAxios } from '../api/saasAxios';
import { useToast } from '../context/ToastContext';
import { getLaravelBaseUrl } from '../runtimeBase';

const REQUIRED_COLUMNS = [
  'employee_first_name',
  'employee_last_name',
  'employee_email',
  'employee_phone',
  'employee_address1',
  'employee_city',
  'employee_state',
  'employee_postalcode',
  'company_name',
  'company_receipient_name',
  'company_email',
  'company_phone',
  'company_address1',
  'company_city',
  'company_state',
  'company_postalcode',
  'type_of_equipment',
  'return_service',
] as const;

export function OrderBulkImportPage() {
  const navigate = useNavigate();
  const { showToast } = useToast();
  const inputRef = useRef<HTMLInputElement>(null);
  const [uploading, setUploading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [dragOver, setDragOver] = useState(false);

  const templateHref = `${getLaravelBaseUrl()}/download-filecsv`;

  const assignFile = useCallback((file: File | null) => {
    setSelectedFile(file);
    const el = inputRef.current;
    if (!el) return;
    if (!file) {
      el.value = '';
      return;
    }
    const dt = new DataTransfer();
    dt.items.add(file);
    el.files = dt.files;
  }, []);

  async function uploadFile(file: File) {
    setError(null);
    setSuccess(null);
    setUploading(true);
    const fd = new FormData();
    fd.append('csvFile', file);
    try {
      const { data } = await saasAxios.post<{ message?: string }>('/create-bycsv/order/', fd);
      const msg = data?.message ?? 'CSV processed successfully.';
      setSuccess(msg);
      showToast(msg, 'success');
      navigate('/orders/in-progress');
    } catch (err: unknown) {
      const ax = err as { response?: { data?: { message?: string } } };
      const msg =
        ax.response?.data?.message ??
        (err instanceof Error ? err.message : 'Upload failed. Check your CSV columns and try again.');
      setError(msg);
      showToast(msg, 'default');
    } finally {
      setUploading(false);
    }
  }

  function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    const f = selectedFile ?? inputRef.current?.files?.[0];
    if (!f) {
      setError('Please choose a CSV file.');
      return;
    }
    void uploadFile(f);
  }

  function onInputChange(e: React.ChangeEvent<HTMLInputElement>) {
    const f = e.target.files?.[0];
    if (f && (f.type === 'text/csv' || f.name.toLowerCase().endsWith('.csv'))) {
      assignFile(f);
      setError(null);
    } else if (f) {
      assignFile(null);
      setError('Please select a .csv file.');
    }
  }

  function onDrop(e: React.DragEvent) {
    e.preventDefault();
    setDragOver(false);
    const f = e.dataTransfer.files?.[0];
    if (!f) return;
    if (f.type === 'text/csv' || f.name.toLowerCase().endsWith('.csv')) {
      assignFile(f);
      setError(null);
    } else {
      setError('Please drop a .csv file.');
    }
  }

  function openPicker() {
    if (!uploading) inputRef.current?.click();
  }

  function removeFile() {
    assignFile(null);
    setError(null);
  }

  /** Dashboard theme accent (Settings → theme), not a fixed green */
  const accent = 'var(--brand-primary, #6366f1)';
  const accentRgb = 'var(--brand-primary-rgb, 99, 102, 241)';
  const pillBg = `rgba(${accentRgb}, 0.14)`;
  const pillText = 'var(--brand-primary, #6366f1)';
  const infoCardBg =
    'color-mix(in srgb, var(--brand-primary, #6366f1) 8%, var(--input-bg, #ffffff))';
  const infoCardBgDark = `rgba(${accentRgb}, 0.1)`;
  const accentTint = `rgba(${accentRgb}, 0.12)`;
  const accentTintDrop = `rgba(${accentRgb}, 0.07)`;
  const accentShadow = `rgba(${accentRgb}, 0.35)`;

  return (
    <div className="content-wrapper">
      <style>{`
        @keyframes order-bulk-spin {
          to { transform: rotate(360deg); }
        }
        .order-bulk-import__spin {
          animation: order-bulk-spin 0.85s linear infinite;
        }
        [data-theme='dark'] .order-bulk-import__info-card {
          background: ${infoCardBgDark} !important;
          border-color: rgba(var(--brand-primary-rgb, 99, 102, 241), 0.24) !important;
        }
        [data-theme='dark'] .order-bulk-import__pill {
          background: rgba(var(--brand-primary-rgb, 99, 102, 241), 0.22) !important;
          color: color-mix(in srgb, var(--brand-primary, #a5b4fc) 82%, #ffffff) !important;
        }
        [data-theme='dark'] .order-bulk-import__drop {
          background: rgba(255,255,255,0.03) !important;
        }
      `}</style>

      <div
        className="card reveal active order-bulk-import"
        style={{
          maxWidth: 800,
          margin: '0 auto',
          padding: 0,
          overflow: 'hidden',
          borderRadius: 'var(--radius-lg, 16px)',
          boxShadow: 'var(--shadow-card, 0 4px 24px rgba(15, 23, 42, 0.08))',
          border: '1px solid var(--border-color, rgba(0,0,0,0.06))',
        }}
      >
        {/* Header */}
        <div
          style={{
            padding: '28px 32px 24px',
            borderBottom: '1px solid var(--border-color)',
          }}
        >
          <Link
            to="/orders/new"
            className="btn btn-outline btn-sm"
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              gap: 8,
              marginBottom: 20,
              fontWeight: 500,
              borderColor: 'var(--border-color)',
              background: 'transparent',
              color: 'var(--text-muted)',
            }}
          >
            <ArrowLeft size={16} strokeWidth={2} />
            Back to create order
          </Link>
          <h1
            className="page-title"
            style={{
              fontSize: 'clamp(1.35rem, 2.5vw, 1.75rem)',
              fontWeight: 700,
              letterSpacing: '-0.02em',
              margin: 0,
              lineHeight: 1.2,
            }}
          >
            Bulk import (CSV)
          </h1>
          <p
            style={{
              color: 'var(--text-muted)',
              fontSize: 15,
              lineHeight: 1.55,
              marginTop: 10,
              marginBottom: 0,
              maxWidth: 560,
            }}
          >
            Upload one CSV with employee and return-address columns. Rows are processed with the
            same rules as the classic bulk screen.
          </p>
        </div>

        {/* Body */}
        <div style={{ padding: '28px 32px 32px' }}>
          {/* Required columns */}
          <div
            className="order-bulk-import__info-card"
            style={{
              padding: 20,
              borderRadius: 'var(--radius-md, 12px)',
              background: infoCardBg,
              border: `1px solid rgba(${accentRgb}, 0.22)`,
              marginBottom: 28,
            }}
          >
            <div style={{ display: 'flex', alignItems: 'flex-start', gap: 12, marginBottom: 14 }}>
              <div
                style={{
                  flexShrink: 0,
                  width: 36,
                  height: 36,
                  borderRadius: 10,
                  background: accentTint,
                  display: 'grid',
                  placeItems: 'center',
                  color: accent,
                }}
              >
                <Info size={18} strokeWidth={2} aria-hidden />
              </div>
              <div style={{ minWidth: 0 }}>
                <h2
                  style={{
                    fontSize: 14,
                    fontWeight: 700,
                    textTransform: 'uppercase',
                    letterSpacing: '0.04em',
                    color: 'var(--text-main)',
                    margin: 0,
                    marginBottom: 4,
                  }}
                >
                  Required columns
                </h2>
                <p style={{ margin: 0, fontSize: 13, color: 'var(--text-muted)', lineHeight: 1.45 }}>
                  Your CSV header row must include every field below (exact names).
                </p>
              </div>
            </div>
            <div
              style={{
                display: 'flex',
                flexWrap: 'wrap',
                gap: 8,
              }}
            >
              {REQUIRED_COLUMNS.map((col) => (
                <span
                  key={col}
                  className="order-bulk-import__pill"
                  style={{
                    display: 'inline-block',
                    padding: '6px 11px',
                    borderRadius: 999,
                    fontSize: 12,
                    fontWeight: 600,
                    background: pillBg,
                    color: pillText,
                    fontFamily: 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace',
                  }}
                >
                  {col}
                </span>
              ))}
            </div>
          </div>

          <div
            style={{
              height: 1,
              background: 'var(--border-color)',
              marginBottom: 24,
              opacity: 0.85,
            }}
          />

          {/* Download */}
          <div style={{ marginBottom: 28 }}>
            <a
              href={templateHref}
              className="order-bulk-import__download-btn"
              style={{
                display: 'inline-flex',
                alignItems: 'center',
                gap: 8,
                padding: '10px 18px',
                borderRadius: 'var(--radius-md, 10px)',
                border: `2px solid ${accent}`,
                color: accent,
                background: 'transparent',
                fontSize: 14,
                fontWeight: 600,
                textDecoration: 'none',
                transition:
                  'background var(--dur-base, 220ms) var(--ease-out), color var(--dur-base, 220ms) var(--ease-out)',
              }}
              onMouseEnter={(e) => {
                e.currentTarget.style.background = accent;
                e.currentTarget.style.color = 'var(--on-brand-primary, #fff)';
              }}
              onMouseLeave={(e) => {
                e.currentTarget.style.background = 'transparent';
                e.currentTarget.style.color = accent;
              }}
            >
              <Download size={18} strokeWidth={2} aria-hidden />
              Download Sample Template
            </a>
          </div>

          {error ? (
            <div
              role="alert"
              style={{
                color: 'crimson',
                fontSize: 14,
                marginBottom: 20,
                padding: '14px 16px',
                borderRadius: 'var(--radius-md)',
                background: 'rgba(220, 38, 38, 0.08)',
                border: '1px solid rgba(220, 38, 38, 0.2)',
              }}
            >
              {error}
            </div>
          ) : null}
          {success ? (
            <div
              style={{
                color: accent,
                fontSize: 14,
                marginBottom: 20,
                padding: '14px 16px',
                borderRadius: 'var(--radius-md)',
                background: `rgba(${accentRgb}, 0.12)`,
                border: `1px solid rgba(${accentRgb}, 0.28)`,
              }}
            >
              {success}
            </div>
          ) : null}

          <form onSubmit={onSubmit}>
            <div className="form-group" style={{ marginBottom: 20 }}>
              <label
                className="form-label"
                htmlFor="bulk-csv-file"
                style={{ fontWeight: 600, marginBottom: 10 }}
              >
                CSV file
              </label>
              <input
                id="bulk-csv-file"
                ref={inputRef}
                type="file"
                name="csvFile"
                accept=".csv,text/csv"
                style={{ position: 'absolute', width: 1, height: 1, opacity: 0, pointerEvents: 'none' }}
                tabIndex={-1}
                disabled={uploading}
                onChange={onInputChange}
              />
              <button
                type="button"
                disabled={uploading}
                onClick={openPicker}
                onKeyDown={(e) => {
                  if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    openPicker();
                  }
                }}
                onDragOver={(e) => {
                  e.preventDefault();
                  setDragOver(true);
                }}
                onDragLeave={() => setDragOver(false)}
                onDrop={onDrop}
                className="order-bulk-import__drop"
                style={{
                  width: '100%',
                  minHeight: 168,
                  padding: 28,
                  borderRadius: 'var(--radius-md, 12px)',
                  border: `2px dashed ${dragOver ? accent : 'var(--border-color)'}`,
                  background: dragOver ? accentTintDrop : 'var(--surface-muted, rgba(0,0,0,0.02))',
                  cursor: uploading ? 'not-allowed' : 'pointer',
                  transition:
                    'border-color var(--dur-base, 220ms) var(--ease-out), background var(--dur-base, 220ms) var(--ease-out)',
                  display: 'flex',
                  flexDirection: 'column',
                  alignItems: 'center',
                  justifyContent: 'center',
                  textAlign: 'center',
                  font: 'inherit',
                  color: 'inherit',
                }}
              >
                {!selectedFile ? (
                  <>
                    <div
                      style={{
                        width: 48,
                        height: 48,
                        borderRadius: 12,
                        background: accentTint,
                        display: 'grid',
                        placeItems: 'center',
                        marginBottom: 14,
                        color: accent,
                      }}
                    >
                      <FileText size={24} strokeWidth={1.75} aria-hidden />
                    </div>
                    <div style={{ fontSize: 15, fontWeight: 600, marginBottom: 6 }}>
                      Drag & drop your CSV file here
                    </div>
                    <div style={{ fontSize: 13, color: 'var(--text-muted)' }}>or click to browse</div>
                  </>
                ) : (
                  <div
                    style={{
                      display: 'flex',
                      alignItems: 'center',
                      gap: 12,
                      flexWrap: 'wrap',
                      justifyContent: 'center',
                    }}
                  >
                    <span style={{ color: accent, display: 'inline-flex' }}>
                      <FileText size={22} aria-hidden />
                    </span>
                    <span
                      style={{
                        fontSize: 15,
                        fontWeight: 600,
                        wordBreak: 'break-all',
                        color: 'var(--text-main)',
                      }}
                    >
                      {selectedFile.name}
                    </span>
                    <button
                      type="button"
                      onClick={(e) => {
                        e.stopPropagation();
                        removeFile();
                      }}
                      disabled={uploading}
                      aria-label="Remove file"
                      style={{
                        width: 32,
                        height: 32,
                        borderRadius: 8,
                        border: '1px solid var(--border-color)',
                        background: 'var(--input-bg)',
                        cursor: uploading ? 'not-allowed' : 'pointer',
                        display: 'grid',
                        placeItems: 'center',
                        color: 'var(--text-muted)',
                      }}
                    >
                      <X size={18} />
                    </button>
                  </div>
                )}
              </button>
            </div>

            <button
              type="submit"
              disabled={uploading}
              style={{
                width: '100%',
                display: 'inline-flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: 10,
                padding: '14px 20px',
                borderRadius: 'var(--radius-md, 12px)',
                border: 'none',
                background: accent,
                color: 'var(--on-brand-primary, #fff)',
                fontSize: 15,
                fontWeight: 600,
                cursor: uploading ? 'not-allowed' : 'pointer',
                opacity: uploading ? 0.85 : 1,
                boxShadow: `0 4px 14px ${accentShadow}`,
              }}
            >
              {uploading ? (
                <>
                  <Loader2 size={20} className="order-bulk-import__spin" aria-hidden />
                  Processing…
                </>
              ) : (
                <>
                  <Upload size={20} strokeWidth={2} aria-hidden />
                  Upload & Process
                </>
              )}
            </button>
          </form>
        </div>
      </div>
    </div>
  );
}
