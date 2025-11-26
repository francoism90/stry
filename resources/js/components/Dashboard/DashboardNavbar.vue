<script setup lang="ts">
import AppLogo from '@/components/Ui/AppLogo.vue'
import type { NavigationMenuItem } from '@nuxt/ui'

const items: NavigationMenuItem[] = [
  {
    label: 'Explore',
    to: '/explore',
    icon: 'i-lucide-compass',
  },
  {
    label: 'Library',
    to: '/library',
    icon: 'i-lucide-library',
  },
  {
    label: 'Profile',
    to: '/profile',
    icon: 'i-lucide-user',
  },
]
</script>

<template>
  <UDashboardNavbar :ui="{ root: 'fixed inset-x-0 top-0 z-30 mx-auto bg-default' }">
    <template #left>
      <ULink to="/">
        <AppLogo />
      </ULink>
    </template>

    <template #right>
      <UNavigationMenu
        variant="link"
        :items="items"
        :ui="{
          linkLabel: 'sr-only sm:not-sr-only',
        }"
      />
    </template>
  </UDashboardNavbar>
</template>
