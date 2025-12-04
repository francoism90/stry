<script setup lang="ts">
import UserMenu from '@/components/Ui/UserMenu.vue'
import type { NavigationMenuItem } from '@nuxt/ui'

const items: NavigationMenuItem[] = [
  {
    label: 'Featured',
    to: '/',
  },
  {
    label: 'Newest',
    to: '/newest',
  },
  {
    label: 'Longest',
    to: '/longest',
  },
  {
    label: 'Shortest',
    to: '/shortest',
  },
]
</script>

<template>
  <div class="flex w-full items-center justify-between">
    <UNavigationMenu
      :items="items"
      variant="link"
      class="h-18"
      highlight
    />

    <UserMenu />
  </div>
</template>
