<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

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
      to: '/tags',
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
    :min-size="16"
    :default-size="16"
    :ui="{ footer: 'border-t border-default' }"
  >
    <template #header>
      <div class="hidden"></div>
    </template>

    <template #default="{ collapsed }">
      <UNavigationMenu
        :collapsed="collapsed"
        :ui="{ root: 'gap-3', link: 'py-2' }"
        :items="items"
        orientation="vertical"
      />
    </template>
  </UDashboardSidebar>
</template>
