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
    :min-size="18"
    :max-size="18"
    :default-size="18"
    :ui="{
      root: 'max-h-dvh bg-elevated/25 lg:sticky lg:top-0',
      footer: 'border-t border-default',
    }"
  >
    <template #header>
      <div>foo</div>
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
