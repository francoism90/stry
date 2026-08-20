<script setup lang="ts">
import VideoTranscodedController from '@/actions/App/Web/Videos/Controllers/VideoTranscodedController'
import type { Video } from '@/types'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  video: Video
}>()

const handle = async (close: () => void) =>
  router.post(
    VideoTranscodedController.url(props.video.id),
    {},
    {
      preserveScroll: true,
      onSuccess: () => close(),
    },
  )
</script>

<template>
  <UModal
    title="Import transcodes"
    :ui="{ footer: 'justify-end' }"
  >
    <slot>
      <UButton
        icon="i-lucide-import"
        label="Import all"
        color="neutral"
        variant="outline"
        size="sm"
      />
    </slot>

    <template #body>
      <div class="flex h-24 flex-col gap-2">
        <h3>Are you sure you want to import all completed transcodes?</h3>
        <p class="text-sm text-neutral-500">All completed transcodes will be imported into the video.</p>
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
        label="Import all"
        variant="soft"
        color="primary"
        loading-auto
        @click.prevent="handle(close)"
      />
    </template>
  </UModal>
</template>
