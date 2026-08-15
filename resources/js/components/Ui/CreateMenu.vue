<script setup lang="ts">
import type { DropdownMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

const items = ref<DropdownMenuItem[]>([
  {
    label: 'Video',
    icon: 'i-lucide-file-video',
    to: '/videos/create',
  },
  {
    label: 'Tag',
    icon: 'i-lucide-tag-plus',
    to: '/tags/create',
  },
  {
    label: 'Collection',
    icon: 'i-lucide-folder',
    to: '/collections/create',
  },
])
</script>

<template>
  <UDropdownMenu
    arrow
    :items="items"
  >
    <UButton
      icon="i-lucide-plus"
      label="Create"
      color="neutral"
      variant="outline"
      class="me-2"
    />
  </UDropdownMenu>
</template>
