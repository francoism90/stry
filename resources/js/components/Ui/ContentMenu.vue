<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

const items = ref<NavigationMenuItem[]>([
  {
    label: 'Videos',
    active: true,
    children: [
      {
        label: 'Videos',
        icon: 'i-lucide-file-video',
        to: '/',
        exact: true,
      },
      {
        label: 'Tags',
        icon: 'i-lucide-tag-plus',
        to: '/tags',
      },
      {
        label: 'Collections',
        icon: 'i-lucide-folder',
        to: '/collections',
      },
    ],
  },
])
</script>

<template>
  <UNavigationMenu
    arrow
    content-orientation="vertical"
    color="primary"
    :items="items"
    :ui="{
      root: 'ms-2 [&>div]:min-w-60',
      childItem: 'w-full',
    }"
  />
</template>
