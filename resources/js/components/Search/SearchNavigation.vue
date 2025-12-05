<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

const items: NavigationMenuItem[] = [
  {
    label: 'Videos',
    to: '/search',
  },
  {
    label: 'Tags',
    to: '/tags',
  },
  {
    label: 'Collections',
    to: '/collections',
  },
]
</script>

<template>
  <UNavigationMenu
    :items="items"
    :ui="{
      link: 'justify-center',
    }"
    highlight
    highlight-color="primary"
    orientation="horizontal"
    class="border-default data-[orientation=horizontal]:w-full data-[orientation=horizontal]:border-b"
  />
</template>
