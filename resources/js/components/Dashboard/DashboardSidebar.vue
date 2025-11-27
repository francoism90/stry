<script setup lang="ts">
import UserMenu from '@/components/Ui/UserMenu.vue'
import type { NavigationMenuItem } from '@nuxt/ui'
import TenantMenu from '../Ui/TenantMenu.vue'

const items: NavigationMenuItem[][] = [
  [
    {
      label: 'Home',
      icon: 'i-lucide-house',
      to: '/',
      exact: true,
    },
    {
      label: 'Inbox',
      icon: 'i-lucide-inbox',
      badge: '4',
    },
  ],
  [
    {
      label: 'Videos',
      icon: 'i-lucide-videotape',
      to: '/videos',
    },
    {
      label: 'Tags',
      icon: 'i-lucide-tags',
      to: '/tags',
    },
    {
      label: 'Media',
      icon: 'i-lucide-files',
      to: '/media',
    },
  ],
  [
    {
      label: 'Settings',
      icon: 'i-lucide-settings',
      children: [
        {
          label: 'Horizon',
          to: '/horizon',
          target: '_blank',
        },
        {
          label: 'Telescope',
          to: '/telescope',
          target: '_blank',
        },
      ],
    },
  ],
]
</script>

<template>
  <UDashboardSidebar
    id="default"
    collapsible
    :min-size="16"
    :max-size="16"
    :default-size="16"
    :ui="{
      root: 'bg-elevated/25 lg:sticky lg:top-0 lg:max-h-dvh',
      footer: 'lg:border-t lg:border-default',
    }"
  >
    <template #header="{ collapsed }">
      <TenantMenu :collapsed="collapsed" />
    </template>

    <template #default="{ collapsed }">
      <UDashboardSearchButton
        :collapsed="collapsed"
        class="bg-transparent ring-default"
      />

      <UNavigationMenu
        :collapsed="collapsed"
        :ui="{ root: 'gap-3', link: 'py-2' }"
        :items="items"
        orientation="vertical"
        tooltip
        popover
      />
    </template>

    <template #footer="{ collapsed }">
      <UserMenu :collapsed="collapsed" />
    </template>
  </UDashboardSidebar>
</template>
