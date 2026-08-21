<script setup lang="ts">
import { useAuth } from '@/composables/auth'
import type { NavigationMenuItem } from '@nuxt/ui'
import { computed } from 'vue'
import AppLogo from './AppLogo.vue'

defineProps<{
  mode: 'drawer' | 'slideover' | 'modal'
}>()

const { hasRole } = useAuth()

const items = computed<NavigationMenuItem[][]>(() => [
  [
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
  ],
  ...(hasRole('super-admin')
    ? [
        [
          {
            label: 'Users',
            icon: 'i-lucide-users',
            to: '/users',
          },
          {
            label: 'Transcodes',
            icon: 'i-lucide-film',
            to: '/transcodes',
          },
        ],
      ]
    : []),
])
</script>

<template>
  <UDashboardSidebar
    :mode="mode"
    :default-size="18"
    :resizable="false"
  >
    <template #header>
      <AppLogo />
    </template>

    <UNavigationMenu
      :items="items"
      orientation="vertical"
      :ui="{
        link: 'py-3',
        separator: 'my-1',
      }"
    />
  </UDashboardSidebar>
</template>
