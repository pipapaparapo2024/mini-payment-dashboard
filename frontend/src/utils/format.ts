export function formatMoney(value: number, withCents = false): string {
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'RUB',
    minimumFractionDigits: withCents ? 2 : 0,
    maximumFractionDigits: withCents ? 2 : 0,
  }).format(value)
}

export function actStatusClass(status: string): string {
  return {
    not_sent: 'status-open',
    awaiting_signature: 'status-wait',
    closed: 'status-done',
    needs_attention: 'status-warn',
  }[status] ?? 'status-open'
}
