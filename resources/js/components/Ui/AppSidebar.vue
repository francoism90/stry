<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'
import AppLogo from './AppLogo.vue'

defineProps<{
  mode: 'drawer' | 'slideover' | 'modal'
}>()

const items: NavigationMenuItem[] = [
  {
    label: 'Home',
    icon: 'i-lucide-house',
    to: '/',
    exact: true,
  },
  {
    label: 'Tags',
    icon: 'i-lucide-tags',
    to: '/tags',
  },
  {
    label: 'Collections',
    icon: 'i-lucide-folders',
    to: '/collections',
  },
]
</script>

<template>
  <UDashboardSidebar
    :mode="mode"
    :ui="{
      root: 'max-w-xs',
      content: 'max-w-xs',
      overlay: 'lg:block',
    }"
  >
    <template #header>
      <AppLogo />
    </template>

    <UNavigationMenu
      :items="items"
      orientation="vertical"
      :ui="{
        link: 'py-3',
      }"
    />
  </UDashboardSidebar>
</template>
