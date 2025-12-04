<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'
import FeedFilterModal from './FeedFilterModal.vue'

const items = ref<NavigationMenuItem[]>([
  {
    label: 'Featured',
    to: '/docs/getting-started',
    active: true,
  },
  {
    label: 'Newest',
    to: '/docs/components',
  },
  {
    label: 'Longest',
    to: '/docs/longest',
  },
  {
    label: 'Shortest',
    to: '/docs/shortest',
  },
])
</script>

<template>
  <div class="flex h-16 w-full items-center justify-between gap-3">
    <UNavigationMenu
      color="neutral"
      :items="items"
      :ui="{
        root: 'w-full px-0 sm:px-0',
        list: 'gap-1',
        link: 'px-4 py-2 before:rounded-full',
      }"
    />

    <FeedFilterModal />
  </div>
</template>
