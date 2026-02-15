<script setup lang="ts">
import { destroy } from '@/actions/App/Admin/Videos/Controllers/VideoController'
import type { Video } from '@/types'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  item: Video
}>()

const remove = async () => router.delete(destroy.url(props.item.id))
</script>

<template>
  <UModal
    :title="item.title"
    :ui="{ footer: 'justify-end' }"
  >
    <slot>
      <UButton
        label="Delete video"
        color="error"
        variant="soft"
      />
    </slot>

    <template #body>
      <div class="flex h-24 flex-col gap-2">
        <h3>Are you sure you want to delete this video?</h3>
        <p class="text-sm text-neutral-500">
          This action cannot be undone. All associated data will be permanently removed.
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
        label="Delete video"
        variant="soft"
        color="error"
        loading-auto
        @click.prevent="remove"
      />
    </template>
  </UModal>
</template>
