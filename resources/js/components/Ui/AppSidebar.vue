<script setup lang="ts">
import AppLogo from '@/components/Ui/AppLogo.vue'
import { useAuth } from '@/composables/auth'
import type { NavigationMenuItem } from '@nuxt/ui'
import { computed } from 'vue'

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
            label: 'Library',
            icon: 'i-lucide-library-big',
            to: '/videos',
            exact: true,
          },
          {
            label: 'Users',
            icon: 'i-lucide-users',
            to: '/users',
            exact: true,
          },
          {
            label: 'Transcodes',
            icon: 'i-lucide-film',
            to: '/transcodes',
            exact: true,
          },
        ],
      ]
    : []),
])
</script>

<template>
  <UDashboardSidebar
    :mode="mode"
    :default-size="16"
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
