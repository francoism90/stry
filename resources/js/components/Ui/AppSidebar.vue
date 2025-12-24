<script setup lang="ts">
import AppLogo from '@/components/Ui/AppLogo.vue'
import type { NavigationMenuItem } from '@nuxt/ui'

const links: NavigationMenuItem[][] = [
  [
    {
      label: 'All',
      icon: 'i-lucide-home',
      to: '/',
      exact: true,
    },
    {
      label: 'Tags',
      icon: 'i-lucide-tags',
      to: '/tags',
    },
  ],
  [
    {
      label: 'Starred',
      icon: 'i-lucide-star',
      to: '/starred',
    },
    {
      label: 'Watched',
      icon: 'i-lucide-clock',
      to: '/watched',
    },
  ],
  [
    {
      label: 'Profile',
      icon: 'i-lucide-user',
      to: '/profile',
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
      root: 'border-0 px-2 lg:sticky lg:top-0 lg:max-h-dvh',
      header: 'flex h-24 items-center',
    }"
  >
    <template #header>
      <AppLogo />
    </template>

    <template #default="{ collapsed }">
      <UNavigationMenu
        :collapsed="collapsed"
        :items="links"
        :ui="{
          root: 'gap-3',
          link: 'gap-2 px-0 py-3',
        }"
        orientation="vertical"
        variant="link"
        tooltip
        popover
      />
    </template>
  </UDashboardSidebar>
</template>
