<script setup lang="ts">
import type { DropdownMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

const items = ref<DropdownMenuItem[]>([
  {
    label: 'Video',
    icon: 'i-lucide-file-video',
    to: '/videos',
  },
  {
    label: 'Tag',
    icon: 'i-lucide-tag-plus',
    to: '/tags',
  },
  {
    label: 'Collection',
    icon: 'i-lucide-folder',
    to: '/collections',
  },
])
</script>

<template>
  <UDropdownMenu
    v-slot="{ open }"
    :modal="false"
    :items="items"
  >
    <UButton
      label="Videos"
      variant="subtle"
      trailing-icon="i-lucide-chevron-down"
      size="sm"
      :class="['ms-1.5', open && 'bg-primary/15']"
      :ui="{
        trailingIcon: ['transition-transform duration-200', open ? 'rotate-180' : undefined].filter(Boolean).join(' '),
      }"
    />
  </UDropdownMenu>
</template>
