<script setup lang="ts">
import type { Media } from '@/types'

interface Props {
  item: Media
}

defineProps<Props>()
</script>

<template>
  <UPageCard
    variant="soft"
    :ui="{ container: 'gap-x-4 gap-y-2 p-3 sm:p-4', title: 'line-clamp-1 text-sm capitalize' }"
    :title="item.name"
  >
    <template #description>
      <dl class="details text-xs font-light tracking-tight text-neutral-300">
        <dt class="sr-only">Mime Type</dt>
        <dd>{{ item.mime_type }}</dd>
        <dt class="sr-only">File Size</dt>
        <dd>{{ item.file_size }}</dd>
      </dl>
    </template>
  </UPageCard>
</template>
