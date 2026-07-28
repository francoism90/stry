<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import type { DropdownMenuItem } from '@nuxt/ui'
import { computed, ref } from 'vue'

const page = usePage()

const items = ref<DropdownMenuItem[]>([
  {
    label: 'Video',
    icon: 'i-lucide-file-video',
    to: '/',
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

const currentItem = computed(() =>
  (items.value as { to?: string; label?: string }[]).find((item) =>
    item.to === '/' ? page.url === '/' : page.url.startsWith(item.to ?? ''),
  ),
)
</script>

<template>
  <UDropdownMenu
    v-slot="{ open }"
    :modal="false"
    :items="items"
  >
    <UButton
      :label="currentItem?.label ?? 'Videos'"
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
