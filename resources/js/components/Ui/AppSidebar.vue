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
    active: true,
  },
  {
    label: 'Inbox',
    icon: 'i-lucide-inbox',
  },
  {
    label: 'Contacts',
    icon: 'i-lucide-users',
  },
]
</script>

<template>
  <UDashboardSidebar
    :mode="mode"
    :ui="{
      root: 'max-w-xs lg:hidden',
      content: 'max-w-xs lg:flex',
      overlay: 'lg:block',
    }"
  >
    <template #header>
      <AppLogo />
    </template>

    <UNavigationMenu
      :items="items"
      orientation="vertical"
    />
  </UDashboardSidebar>
</template>
