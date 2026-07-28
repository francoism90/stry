<script setup lang="ts">
import AppLogo from '@/components/Ui/AppLogo.vue'
import ContentMenu from '@/components/Ui/ContentMenu.vue'
import UserMenu from '@/components/Ui/UserMenu.vue'
import UserNotifications from '@/components/Ui/UserNotifications.vue'
import type { NavigationMenuItem } from '@nuxt/ui'

const items: NavigationMenuItem[] = [
  {
    label: 'Videos',
    to: '/',
    exact: true,
  },
  {
    label: 'Collections',
    to: '/collections',
  },
  {
    label: 'Tags',
    to: '/tags',
  },
]
</script>

<template>
  <header class="sticky top-0 z-40 w-full bg-neutral-900/80 backdrop-blur-md backdrop-saturate-150">
    <UDashboardNavbar :toggle="false">
      <template #left>
        <AppLogo />

        <UNavigationMenu
          :items="items"
          variant="link"
        />
      </template>

      <template #right>
        <ContentMenu />
        <UserNotifications />
        <UserMenu />
      </template>
    </UDashboardNavbar>

    <slot />
  </header>
</template>
