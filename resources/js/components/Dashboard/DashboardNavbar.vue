<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'
import AppLogo from '../Ui/AppLogo.vue'

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
  <UDashboardNavbar
    :toggle="false"
    :ui="{ root: 'fixed inset-0 top-0 z-30 mx-auto w-full max-w-(--ui-container) bg-default' }"
  >
    <template #left>
      <ULink to="/">
        <AppLogo />
      </ULink>
    </template>

    <template #right>
      <UNavigationMenu
        :items="items"
        variant="link"
        :ui="{
          linkLabel: 'sr-only sm:not-sr-only',
        }"
      />
    </template>
  </UDashboardNavbar>
</template>
