<script setup lang="ts">
import { destroy } from '@/actions/App/Web/Videos/Controllers/VideoController'
import type { Video } from '@/types'
import { router } from '@inertiajs/vue3'

interface Props {
  item: Video
}

const props = defineProps<Props>()

const remove = async () =>
  router.delete(destroy.url({ video: props.item.id }), {
    onSuccess: () => router.visit('/videos'),
  })
</script>

<template>
  <UModal
    :title="item.title"
    :description="item.summary || 'No description available.'"
    :ui="{ footer: 'justify-end' }"
  >
    <UButton
      label="Delete video"
      color="warning"
      variant="soft"
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
        @click.prevent="remove"
      />
    </template>
  </UModal>
</template>
