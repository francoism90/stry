<script setup lang="ts">
import { destroy } from '@/actions/App/Admin/Videos/Controllers/VideoPlaylistController'
import type { Playlist, Video } from '@/types'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  video: Video
  item: Playlist
}>()

const remove = async () => router.delete(destroy.url([props.video.id, props.item.id]))
</script>

<template>
  <UModal
    :title="`${item.type} Playlist`"
    :ui="{ footer: 'justify-end' }"
  >
    <UButton
      label="Delete playlist"
      color="error"
      variant="soft"
    />

    <template #body>
      <div class="flex h-24 flex-col gap-2">
        <h3>Are you sure you want to delete this playlist?</h3>
        <p class="text-sm text-neutral-500">This action cannot be undone. The playlist will be permanently removed.</p>
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
        label="Delete playlist"
        variant="soft"
        color="error"
        loading-auto
        @click.prevent="remove"
      />
    </template>
  </UModal>
</template>
