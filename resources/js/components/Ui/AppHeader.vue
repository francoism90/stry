<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

const items: NavigationMenuItem[] = [
  {
    label: 'Search',
    to: '/search',
    icon: 'i-lucide-search',
  },
  {
    label: 'Profile',
    to: '/profile',
    icon: 'i-lucide-user',
  },
]
</script>

<template>
  <div class="flex h-20 w-full items-center justify-between gap-4 border-b border-muted">
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
