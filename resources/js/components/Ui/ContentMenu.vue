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
    content-orientation="vertical"
    type="single"
    color="primary"
    :items="items"
    :ui="{
      root: 'ms-2',
      viewport: 'min-w-60',
      childLink: 'py-1.5',
      childLinkIcon: 'size-4.5',
    }"
  />
</template>
