<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

const items = ref<NavigationMenuItem[][]>([
  [
    {
      label: 'Guide',
      icon: 'i-lucide-book-open',
      to: '/docs/getting-started',
    },
    {
      label: 'Composables',
      icon: 'i-lucide-database',
      to: '/docs/composables',
    },
    {
      label: 'Components',
      icon: 'i-lucide-box',
      to: '/docs/components',
      active: true,
    },
  ],
  [
    {
      label: 'GitHub',
      icon: 'i-simple-icons-github',
      badge: '3.8k',
      to: 'https://github.com/nuxt/ui',
      target: '_blank',
    },
  ],
])
</script>

<template>
  <div class="sticky top-0 z-50 w-full border-b border-muted bg-default">
    <UNavigationMenu
      color="neutral"
      :items="items"
      :ui="{
        root: 'h-16 w-full px-4 sm:px-6',
      }"
    />
  </div>
</template>
