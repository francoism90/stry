<script setup lang="ts">
import DashboardTenantMenu from '@/components/Admin/DashboardTenantMenu.vue'
import DashboardUserMenu from '@/components/Admin/DashboardUserMenu.vue'
import type { NavigationMenuItem } from '@nuxt/ui'
import UserNotifications from '../Ui/UserNotifications.vue'

const links: NavigationMenuItem[][] = [
  [
    {
      label: 'Dashboard',
      icon: 'i-lucide-layout-dashboard',
      to: '/admin',
      exact: true,
    },
  ],
  [
    {
      label: 'Videos',
      icon: 'i-lucide-videotape',
      to: '/admin/videos',
    },
    {
      label: 'Tags',
      icon: 'i-lucide-tags',
      to: '/admin/tags',
    },
    {
      label: 'Users',
      icon: 'i-lucide-users',
      to: '/admin/users',
    },
  ],
  [
    {
      label: 'Settings',
      icon: 'i-lucide-settings',
      children: [
        {
          label: 'General',
          to: '/admin/settings/general',
        },
        {
          label: 'Playback',
          to: '/admin/settings/playback',
        },
        {
          label: 'Logs',
          to: '/admin/settings/logs',
        },
        {
          label: 'Tasks',
          to: '/admin/settings/tasks',
        },
      ],
    },
  ],
]
</script>

<template>
  <UDashboardSidebar
    id="admin"
    collapsible
    :min-size="15"
    :max-size="15"
    :default-size="15"
    :ui="{
      root: 'bg-elevated/25 lg:sticky lg:top-0 lg:max-h-dvh',
      footer: 'lg:border-default lg:border-t',
    }"
  >
    <template #header="{ collapsed }">
      <DashboardTenantMenu :collapsed="collapsed" />
      <UserNotifications />
    </template>

    <template #default="{ collapsed }">
      <UNavigationMenu
        :collapsed="collapsed"
        :ui="{ root: 'gap-3', link: 'py-2' }"
        :items="links"
        orientation="vertical"
        tooltip
        popover
      />
    </template>

    <template #footer="{ collapsed }">
      <DashboardUserMenu :collapsed="collapsed" />
    </template>
  </UDashboardSidebar>
</template>
