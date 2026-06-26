<script setup lang="ts">
import { computed } from 'vue'
import { useDashboardStore } from '@/stores/dashboard'
import { formatMoney } from '@/utils/format'

const store = useDashboardStore()

const cards = computed(() => {
  const s = store.summary
  if (!s) return []
  return [
    ['Проектов / юрлиц', s.projects_count, 'в текущем фильтре'],
    ['Оплат', s.payments_count, 'поступления по проектам'],
    ['Получено', formatMoney(s.total_amount), 'все оплаченные этапы'],
    ['Акты отправлены', formatMoney(s.sent_acts_amount), `${s.payments_count - s.unsent_acts_count} из ${s.payments_count}`],
    ['Акты подписаны', formatMoney(s.closed_acts_amount), `${s.payments_count - (s.unsent_acts_count + s.awaiting_signature_count)} закрытых`],
    ['Не закрыто актами', formatMoney(s.unclosed_acts_amount), `${s.needs_attention_count} требуют внимания`],
  ]
})
</script>

<template>
  <section v-if="store.summary" class="cards">
    <article v-for="card in cards" :key="card[0]" class="card">
      <div class="label">{{ card[0] }}</div>
      <div class="value">{{ card[1] }}</div>
      <div class="hint">{{ card[2] }}</div>
    </article>
  </section>
</template>
