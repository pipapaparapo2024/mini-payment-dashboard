<script setup lang="ts">
import { computed, onMounted } from 'vue'
import FilterToolbar from '@/components/FilterToolbar.vue'
import PaymentsTable from '@/components/PaymentsTable.vue'
import ProjectSidebar from '@/components/ProjectSidebar.vue'
import ProjectsTable from '@/components/ProjectsTable.vue'
import SummaryCards from '@/components/SummaryCards.vue'
import { useDashboardStore } from '@/stores/dashboard'
import { formatMoney } from '@/utils/format'

const store = useDashboardStore()

onMounted(() => {
  void store.init()
})

const finalSummary = computed(() => {
  const s = store.summary
  const top = store.projects[0]
  if (!s || !top) return 'Нет данных по выбранным фильтрам.'
  return `Учтено ${s.payments_count} проектных оплат по ${s.projects_count} юрлицам на сумму ${formatMoney(s.total_amount)}. Самый крупный проект — ${top.name}: ${formatMoney(top.total_amount)}. Закрыто подписанными актами: ${formatMoney(s.closed_acts_amount)}, ожидает закрытия: ${formatMoney(s.unclosed_acts_amount)}.`
})

const periodLabel = computed(() => {
  const p = store.filterOptions?.statement_period
  if (!p) return ''
  return `${p.from} — ${p.to}`
})
</script>

<template>
  <div class="wrap">
    <section class="hero">
      <div>
        <h1>Дашборд оплат по проектам и актам</h1>
        <p class="lead">
          Сводка по проектным поступлениям из банковской выписки. Статусы актов сохраняются на сервере.
        </p>
      </div>
      <aside v-if="periodLabel" class="hero-note">
        <b>Период выписки: {{ periodLabel }}</b>
        <div class="muted small">Источник: обработанный PDF · порог внимания: 14 дней</div>
      </aside>
    </section>

    <div v-if="store.error" class="error">{{ store.error }}</div>
    <div v-if="store.loading" class="muted">Загрузка...</div>

    <SummaryCards />
    <FilterToolbar />

    <section class="grid">
      <ProjectSidebar />
      <main class="panel">
        <div class="panel-head">
          <h2>Оплаты и закрывающие документы</h2>
          <span v-if="store.summary" class="muted small">
            {{ store.summary.payments_count }} оплат · {{ formatMoney(store.summary.total_amount) }}
          </span>
        </div>
        <div class="tabs">
          <button
            class="tab"
            :class="{ active: store.activeTab === 'payments' }"
            type="button"
            @click="store.activeTab = 'payments'"
          >
            Оплаты
          </button>
          <button
            class="tab"
            :class="{ active: store.activeTab === 'projects' }"
            type="button"
            @click="store.activeTab = 'projects'"
          >
            Сводка по юрлицам
          </button>
        </div>
        <PaymentsTable v-if="store.activeTab === 'payments'" />
        <ProjectsTable v-else />
      </main>
    </section>

    <section class="summary-text">{{ finalSummary }}</section>
  </div>
</template>
