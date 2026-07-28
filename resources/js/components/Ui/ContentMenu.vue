<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import type { DropdownMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

const items = ref<DropdownMenuItem[][]>([
  [
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
  ],
  [
    {
      label: 'Log out',
      icon: 'i-lucide-log-out',
      onClick: () => router.post('/logout'),
    },
  ],
])
</script>

<template>
  <UDropdownMenu :items="items">
    <UButton
      icon="i-lucide-plus"
      label="Create"
      color="neutral"
      variant="outline"
      class="me-2"
    />
  </UDropdownMenu>
</template>
