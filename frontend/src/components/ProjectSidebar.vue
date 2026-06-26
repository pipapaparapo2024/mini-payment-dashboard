<script setup lang="ts">
import { useDashboardStore } from '@/stores/dashboard'
import { formatMoney } from '@/utils/format'

const store = useDashboardStore()
</script>

<template>
  <aside class="panel">
    <div class="panel-head">
      <h2>Проекты / юрлица</h2>
      <span class="muted small">{{ store.projects.length }} юрлиц</span>
    </div>
    <div class="panel-body">
      <div class="project-list">
        <div
          v-for="project in store.projects"
          :key="project.id"
          class="project-card"
          :class="{ active: store.filters.project_id === String(project.id) }"
          @click="store.selectProject(store.filters.project_id === String(project.id) ? null : project.id)"
        >
          <div class="top">
            <div class="name">{{ project.name }}</div>
            <div class="sum">{{ formatMoney(project.total_amount) }}</div>
          </div>
          <div class="meta">
            {{ project.legal_entity.inn }} · {{ project.payments_count }} оплат
          </div>
          <div class="progress" :title="`Подписано: ${project.closure_percent}%`">
            <i :style="{ width: `${project.closure_percent}%` }" />
          </div>
          <div class="meta">Подписано: {{ formatMoney(project.signed_acts_amount) }} / {{ project.closure_percent }}%</div>
        </div>
        <p v-if="!store.projects.length" class="muted">Нет проектов по фильтру</p>
      </div>
    </div>
  </aside>
</template>
