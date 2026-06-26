<script setup lang="ts">
import { useDashboardStore } from '@/stores/dashboard'

const store = useDashboardStore()
</script>

<template>
  <section class="toolbar">
    <div class="filters">
      <div>
        <label>Поиск</label>
        <input
          :value="store.filters.search"
          type="search"
          placeholder="Проект, ИНН, этап, счёт..."
          @input="store.setFilter('search', ($event.target as HTMLInputElement).value)"
        />
      </div>
      <div>
        <label>Проект</label>
        <select
          :value="store.filters.project_id"
          @change="store.setFilter('project_id', ($event.target as HTMLSelectElement).value)"
        >
          <option value="">Все проекты</option>
          <option v-for="p in store.filterOptions?.projects ?? []" :key="p.id" :value="p.id">{{ p.name }}</option>
        </select>
      </div>
      <div>
        <label>Этап</label>
        <select
          :value="store.filters.service_stage"
          @change="store.setFilter('service_stage', ($event.target as HTMLSelectElement).value)"
        >
          <option value="">Все этапы</option>
          <option v-for="stage in store.filterOptions?.service_stages ?? []" :key="stage" :value="stage">{{ stage }}</option>
        </select>
      </div>
      <div>
        <label>Дата с</label>
        <input
          :value="store.filters.date_from"
          type="date"
          @change="store.setFilter('date_from', ($event.target as HTMLInputElement).value)"
        />
      </div>
      <div>
        <label>Дата по</label>
        <input
          :value="store.filters.date_to"
          type="date"
          @change="store.setFilter('date_to', ($event.target as HTMLInputElement).value)"
        />
      </div>
      <div>
        <label>Статус акта</label>
        <select
          :value="store.filters.act_status"
          @change="store.setFilter('act_status', ($event.target as HTMLSelectElement).value)"
        >
          <option value="">Все</option>
          <option v-for="s in store.filterOptions?.act_statuses ?? []" :key="s.value" :value="s.value">{{ s.label }}</option>
        </select>
      </div>
      <div>
        <label>Акт отправлен</label>
        <select
          :value="store.filters.is_sent"
          @change="store.setFilter('is_sent', ($event.target as HTMLSelectElement).value)"
        >
          <option value="">Все</option>
          <option value="yes">Да</option>
          <option value="no">Нет</option>
        </select>
      </div>
      <div>
        <label>Акт подписан</label>
        <select
          :value="store.filters.is_signed"
          @change="store.setFilter('is_signed', ($event.target as HTMLSelectElement).value)"
        >
          <option value="">Все</option>
          <option value="yes">Да</option>
          <option value="no">Нет</option>
        </select>
      </div>
    </div>
    <div class="actions">
      <button class="primary" type="button" @click="store.resetFilters()">Сбросить фильтры</button>
      <button class="good" type="button" @click="store.markVisibleSent()">Отметить видимые отправленными</button>
      <button class="good" type="button" @click="store.markVisibleSigned()">Отметить видимые подписанными</button>
      <button type="button" @click="store.exportCsv()">Экспорт CSV</button>
    </div>
  </section>
</template>
