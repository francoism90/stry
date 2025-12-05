<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

const items: NavigationMenuItem[] = [
  {
    label: 'Discover',
    to: '/discover',
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
  <div class="sticky top-0 z-50 flex h-20 w-full items-center justify-between gap-4 border-b border-muted bg-default px-4 sm:px-6">
    <ULink to="/">
      <span class="font-serif text-sm font-bold text-neutral-400 uppercase">stry</span>
    </ULink>

    <UNavigationMenu
      :items="items"
      variant="link"
      :ui="{
        list: 'gap-4',
        link: 'px-0',
      }"
    />
  </div>
</template>
