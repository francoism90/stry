<script setup lang="ts">
import AppLogo from '@/components/Ui/AppLogo.vue'
import type { NavigationMenuItem } from '@nuxt/ui'

const items: NavigationMenuItem[] = [
  {
    label: 'Profile',
    icon: 'i-lucide-user',
    to: '/profile',
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
      <UDashboardSearchButton />

      <UNavigationMenu
        variant="link"
        class="w-full"
        :items="items"
      />
    </template>
  </UDashboardNavbar>
</template>
