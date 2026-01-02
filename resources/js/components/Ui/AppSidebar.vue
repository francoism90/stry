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
      to: '/history',
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
    collapsible
    :min-size="16"
    :max-size="16"
    :default-size="16"
    :ui="{
      root: 'border-0 px-2 lg:sticky lg:top-0 lg:max-h-dvh',
      header: 'flex h-20 items-center sm:h-24',
      content: 'max-w-xs',
    }"
  >
    <template #header>
      <AppLogo class="w-full" />
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
