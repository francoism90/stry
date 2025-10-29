<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

const items: NavigationMenuItem[] = [
  {
    label: 'Home',
    to: '/',
    icon: 'i-lucide-house',
  },
  {
    label: 'Explore',
    to: '/explore',
    icon: 'i-lucide-compass',
  },
  {
    label: 'Library',
    to: '/library',
    icon: 'i-lucide-book',
  },
  {
    label: 'Profile',
    to: '/profile',
    icon: 'i-lucide-user',
  },
]
</script>

<template>
  <UFooter :ui="{ root: 'border-t border-default bg-default', container: 'py-0 lg:py-0', center: 'my-0', left: 'mt-0' }">
    <UNavigationMenu
      :items="items"
      :ui="{ list: 'gap-3 lg:gap-6', link: 'flex-col' }"
      variant="link"
    />
  </UFooter>
</template>
