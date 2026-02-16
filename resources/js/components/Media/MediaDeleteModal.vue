<script setup lang="ts">
import { destroy } from '@/actions/App/Admin/Videos/Controllers/VideoMediaController';
import type { Media, Video } from '@/types';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
  video: Video
  item: Media
}>()

const remove = async () => router.delete(destroy.url([props.video.id, props.item.id]))
</script>

<template>
  <UModal
    :title="item.name"
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
        <h3>Are you sure you want to delete this media?</h3>
        <p class="text-sm text-neutral-500">
          This action cannot be undone. The media file will be permanently removed.
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
        label="Delete media"
        variant="soft"
        color="error"
        loading-auto
        @click.prevent="remove"
      />
    </template>
  </UModal>
</template>
