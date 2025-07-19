<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

const items = ref<NavigationMenuItem[][]>([
  [
    {
      label: 'Stry',
      to: '/',
      ui: {
        linkLabel: 'font-serif text-neutral-500 font-bold uppercase',
      },
    },
  ],
  [
    {
      label: 'Lists',
      icon: 'i-lucide-library',
      to: '/lists',
      ui: {
        linkLabel: 'sr-only',
      },
    },
    {
      label: 'Search',
      icon: 'i-lucide-search',
      to: '/search',
      ui: {
        linkLabel: 'sr-only',
      },
    },
    {
      label: 'Profile',
      icon: 'i-lucide-user',
      to: '/profile',
      ui: {
        linkLabel: 'sr-only',
      },
    },
  ],
])
</script>

<template>
  <UNavigationMenu
    orientation="horizontal"
    variant="link"
    :items="items"
    :ui="{
      root: 'size-full items-center',
      list: 'inline-flex size-full items-center gap-4 sm:gap-6',
      link: 'px-0',
      linkTrailingIcon: 'hidden',
    }"
  />
</template>
