<script setup lang="ts">
import { useDashboardStore } from '@/stores/dashboard'
import { actStatusClass, formatMoney } from '@/utils/format'

const store = useDashboardStore()

let commentTimers = new Map<number, ReturnType<typeof setTimeout>>()

function onCommentInput(paymentId: number, value: string) {
  const existing = commentTimers.get(paymentId)
  if (existing) clearTimeout(existing)
  commentTimers.set(
    paymentId,
    setTimeout(() => {
      const payment = store.payments.find((p) => p.id === paymentId)
      if (payment) void store.updateComment(payment, value)
    }, 500),
  )
}
</script>

<template>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Дата</th>
          <th>Проект / юрлицо</th>
          <th>ИНН / ОГРН</th>
          <th>Этап</th>
          <th>Счёт / док.</th>
          <th class="money">Сумма</th>
          <th>Акт отправлен</th>
          <th>Акт подписан</th>
          <th>Статус</th>
          <th>Комментарий</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="payment in store.payments" :key="payment.id">
          <td><b>{{ payment.payment_date_ru }}</b></td>
          <td>
            <b>{{ payment.project_name }}</b>
            <div class="muted small">{{ payment.legal_entity.legal_type }} · р/с {{ payment.legal_entity.bank_account }}</div>
          </td>
          <td>
            <div>ИНН {{ payment.legal_entity.inn }}</div>
            <div class="muted small">{{ payment.legal_entity.ogrn }}</div>
          </td>
          <td>
            <span class="stage">{{ payment.service_stage }}</span>
            <div v-if="payment.period" class="muted small">Период: {{ payment.period }}</div>
          </td>
          <td>
            <b>{{ payment.invoice_reference }}</b>
            <div class="muted small">Док. {{ payment.document_number }}</div>
            <div class="muted small">{{ payment.payment_purpose }}</div>
          </td>
          <td class="money">{{ formatMoney(payment.amount, true) }}</td>
          <td>
            <input
              type="checkbox"
              :checked="payment.act.is_sent"
              @change="store.toggleActSent(payment, ($event.target as HTMLInputElement).checked)"
            />
          </td>
          <td>
            <input
              type="checkbox"
              :checked="payment.act.is_signed"
              @change="store.toggleActSigned(payment, ($event.target as HTMLInputElement).checked)"
            />
          </td>
          <td>
            <span class="status" :class="actStatusClass(payment.act.status)">{{ payment.act.status_label }}</span>
          </td>
          <td>
            <input
              type="text"
              :value="payment.act.manager_comment ?? ''"
              placeholder="комментарий менеджера"
              @input="onCommentInput(payment.id, ($event.target as HTMLInputElement).value)"
            />
          </td>
        </tr>
        <tr v-if="!store.payments.length">
          <td colspan="10" class="muted">Нет оплат по текущему фильтру</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
