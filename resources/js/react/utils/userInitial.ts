/** First letter of the first name token; then email; else ? */
export function userInitial(name: string | null | undefined, email: string | null | undefined) {
  const n = String(name ?? '').trim();
  for (const part of n.split(/\s+/)) {
    const m = part.match(/[A-Za-zÀ-ÿ]/);
    if (m) return m[0].toUpperCase();
  }
  const e = String(email ?? '').trim();
  const em = e.match(/[A-Za-zÀ-ÿ]/);
  if (em) return em[0].toUpperCase();
  return '?';
}
