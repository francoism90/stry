<script setup lang="ts">
import type { Video } from '@/types'

interface Props {
  item: Video
}

defineProps<Props>()
</script>

<template>
  <UModal
    :title="item.title"
    :description="item.description || 'No description available.'"
    :ui="{ footer: 'justify-end' }"
  >
    <UButton
      label="Save"
      color="neutral"
      variant="outline"
      icon="i-lucide-bookmark"
    />

    <template #body>
      <div class="flex h-24 flex-col gap-2">
        <h3>Are you sure you want to delete this video?</h3>
        <p class="text-sm text-neutral-500">This action cannot be undone. All associated data will be permanently removed.</p>
      </div>
    </template>

    <template #footer="{ close }">
      <UButton
        label="Cancel"
        color="neutral"
        variant="soft"
        @click.prevent="close"
      />

      <UButton
        label="Delete video"
        variant="soft"
        color="error"
        loading-auto
      />
    </template>
  </UModal>
</template>
