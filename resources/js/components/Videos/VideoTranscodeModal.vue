<script setup lang="ts">
import VideoTranscodeController from '@/actions/App/Api/Videos/Controllers/VideoTranscodeController'
import type { Video } from '@/types'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  item: Video
}>()

const transcode = async (close: () => void) =>
  router.post(
    VideoTranscodeController.url(props.item.id),
    {},
    {
      preserveScroll: true,
      onSuccess: () => close(),
    },
  )
</script>

<template>
  <UModal
    :title="item.title"
    :ui="{ footer: 'justify-end' }"
  >
    <slot>
      <UButton
        icon="i-lucide-trash"
        color="error"
        variant="ghost"
        size="sm"
      />
    </slot>

    <template #body>
      <div class="flex h-24 flex-col gap-2">
        <h3>Are you sure you want to transcode this video?</h3>
        <p class="text-sm text-neutral-500">
          This action will queue a transcode for this video. Depending on the length of the video and your server's
          resources, this may take some time to complete.
        </p>
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
        label="Transcode video"
        variant="soft"
        color="primary"
        loading-auto
        @click.prevent="transcode(close)"
      />
    </template>
  </UModal>
</template>
