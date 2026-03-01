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
      label: 'History',
      icon: 'i-lucide-history',
      to: '/viewed',
    },
    {
      label: 'Liked',
      icon: 'i-lucide-heart',
      to: '/liked',
    },
    {
      label: 'Saved',
      icon: 'i-lucide-bookmark',
      to: '/saved',
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
    :min-size="15"
    :max-size="15"
    :default-size="15"
    :ui="{
      root: 'border-0 px-2 lg:sticky lg:top-0 lg:max-h-dvh',
      header: 'flex h-(--ui-header-height) items-center',
      content: 'max-w-xs',
    }"
  >
    <template #header>
      <AppLogo class="w-full" />
    </template>

    <template #default>
      <UNavigationMenu
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
