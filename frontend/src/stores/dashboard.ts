import { defineStore } from 'pinia'
import {
  bulkUpdateActs,
  fetchFilters,
  fetchPayments,
  fetchProjects,
  fetchSummary,
  updatePaymentAct,
} from '@/api/client'
import type { DashboardFilters, DashboardSummary, FilterOptions, Payment, ProjectAggregate } from '@/types'

const defaultFilters = (): DashboardFilters => ({
  search: '',
  project_id: '',
  service_stage: '',
  date_from: '',
  date_to: '',
  is_sent: '',
  is_signed: '',
  act_status: '',
})

export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    filters: defaultFilters(),
    filterOptions: null as FilterOptions | null,
    summary: null as DashboardSummary | null,
    payments: [] as Payment[],
    projects: [] as ProjectAggregate[],
    activeTab: 'payments' as 'payments' | 'projects',
    loading: false,
    error: '' as string,
  }),

  actions: {
    async init() {
      this.filterOptions = await fetchFilters()
      if (!this.filters.date_from) this.filters.date_from = this.filterOptions.date_from
      if (!this.filters.date_to) this.filters.date_to = this.filterOptions.date_to
      await this.refresh()
    },

    async refresh() {
      this.loading = true
      this.error = ''
      try {
        const [summary, payments, projects] = await Promise.all([
          fetchSummary(this.filters),
          fetchPayments(this.filters),
          fetchProjects(this.filters),
        ])
        this.summary = summary
        this.payments = payments
        this.projects = projects
      } catch (e) {
        this.error = e instanceof Error ? e.message : 'Ошибка загрузки данных'
      } finally {
        this.loading = false
      }
    },

    resetFilters() {
      this.filters = defaultFilters()
      if (this.filterOptions) {
        this.filters.date_from = this.filterOptions.date_from
        this.filters.date_to = this.filterOptions.date_to
      }
      void this.refresh()
    },

    setFilter<K extends keyof DashboardFilters>(key: K, value: DashboardFilters[K]) {
      this.filters[key] = value
      void this.refresh()
    },

    selectProject(projectId: number | null) {
      this.filters.project_id = projectId ? String(projectId) : ''
      void this.refresh()
    },

    async toggleActSent(payment: Payment, value: boolean) {
      await updatePaymentAct(payment.id, { is_sent: value })
      await this.refresh()
    },

    async toggleActSigned(payment: Payment, value: boolean) {
      await updatePaymentAct(payment.id, { is_signed: value })
      await this.refresh()
    },

    async updateComment(payment: Payment, comment: string) {
      await updatePaymentAct(payment.id, { manager_comment: comment })
      payment.act.manager_comment = comment
    },

    async markVisibleSent() {
      await bulkUpdateActs(this.payments.map((p) => p.id), { is_sent: true })
      await this.refresh()
    },

    async markVisibleSigned() {
      await bulkUpdateActs(this.payments.map((p) => p.id), { is_signed: true })
      await this.refresh()
    },

    exportCsv() {
      const header = [
        'Дата',
        'Проект',
        'ИНН',
        'Этап',
        'Сумма',
        'Акт отправлен',
        'Акт подписан',
        'Статус',
        'Комментарий',
      ]
      const rows = this.payments.map((p) => [
        p.payment_date_ru,
        p.project_name,
        p.legal_entity.inn,
        p.service_stage,
        String(p.amount),
        p.act.is_sent ? 'Да' : 'Нет',
        p.act.is_signed ? 'Да' : 'Нет',
        p.act.status_label,
        p.act.manager_comment ?? '',
      ])
      const csv = [header, ...rows]
        .map((row) => row.map((cell) => `"${String(cell).replace(/"/g, '""')}"`).join(';'))
        .join('\n')
      const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' })
      const url = URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = 'payments-export.csv'
      link.click()
      URL.revokeObjectURL(url)
    },
  },
})
