import { createRouter, createWebHistory } from 'vue-router'
import DashboardView from '@/views/DashboardView.vue'

export const router = createRouter({
  history: createWebHistory('/spa/'),
  routes: [{ path: '/', name: 'dashboard', component: DashboardView }],
})
