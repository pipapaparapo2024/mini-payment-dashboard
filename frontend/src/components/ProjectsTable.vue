<script setup lang="ts">
import { useDashboardStore } from '@/stores/dashboard'
import { actStatusClass, formatMoney } from '@/utils/format'

const store = useDashboardStore()
</script>

<template>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Проект / юрлицо</th>
          <th>ИНН</th>
          <th>Оплат</th>
          <th class="money">Получено</th>
          <th class="money">Акты отправлены</th>
          <th class="money">Акты подписаны</th>
          <th>Закрытие</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="project in store.projects" :key="project.id">
          <td><b>{{ project.name }}</b></td>
          <td>{{ project.legal_entity.inn }}</td>
          <td>{{ project.payments_count }}</td>
          <td class="money">{{ formatMoney(project.total_amount) }}</td>
          <td class="money">{{ formatMoney(project.sent_acts_amount) }}</td>
          <td class="money">{{ formatMoney(project.signed_acts_amount) }}</td>
          <td>
            <span class="status" :class="actStatusClass(project.document_flow_status)">
              {{ project.document_flow_status_label }} ({{ project.closure_percent }}%)
            </span>
          </td>
        </tr>
        <tr v-if="!store.projects.length">
          <td colspan="7" class="muted">Нет данных</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
