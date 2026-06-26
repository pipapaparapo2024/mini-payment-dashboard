import axios from 'axios'
import type { DashboardFilters, DashboardSummary, FilterOptions, Payment, ProjectAggregate } from '@/types'

const client = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL ?? '/api/v1',
  headers: { Accept: 'application/json' },
})

function toParams(filters: Partial<DashboardFilters>): Record<string, string> {
  const params: Record<string, string> = {}
  for (const [key, value] of Object.entries(filters)) {
    if (value) params[key] = value
  }
  return params
}

export async function fetchSummary(filters: Partial<DashboardFilters>): Promise<DashboardSummary> {
  const { data } = await client.get<DashboardSummary>('/dashboard/summary', { params: toParams(filters) })
  return data
}

export async function fetchPayments(filters: Partial<DashboardFilters>): Promise<Payment[]> {
  const { data } = await client.get<{ data: Payment[] }>('/payments', { params: toParams(filters) })
  return data.data
}

export async function fetchProjects(filters: Partial<DashboardFilters>): Promise<ProjectAggregate[]> {
  const { data } = await client.get<{ data: ProjectAggregate[] }>('/projects', { params: toParams(filters) })
  return data.data
}

export async function fetchFilters(): Promise<FilterOptions> {
  const { data } = await client.get<FilterOptions>('/filters')
  return data
}

export async function updatePaymentAct(
  paymentId: number,
  payload: Partial<{ is_sent: boolean; is_signed: boolean; manager_comment: string }>,
): Promise<Payment> {
  const { data } = await client.patch<Payment>(`/payments/${paymentId}/act`, payload)
  return data
}

export async function bulkUpdateActs(
  paymentIds: number[],
  payload: Partial<{ is_sent: boolean; is_signed: boolean }>,
): Promise<void> {
  await client.patch('/payments/bulk-act', { payment_ids: paymentIds, ...payload })
}
