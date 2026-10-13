<script setup lang="ts">
import VideoExportController from '@/actions/Modules/Web/Videos/Controllers/VideoExportController'
import type { Video } from '@/types'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps<{
  video: Video
}>()

const deleteAfterExport = ref(false)

const handle = async (close: () => void) =>
  router.post(
    VideoExportController.url(props.video.id),
    { delete: deleteAfterExport.value },
    {
      preserveScroll: true,
      onSuccess: () => close(),
    },
  )
</script>

<template>
  <UModal
    title="Export video"
    :ui="{ footer: 'justify-end' }"
    @after:leave="deleteAfterExport = false"
  >
    <slot>
      <UButton
        icon="i-lucide-folder-output"
        label="Export"
        color="neutral"
        variant="outline"
        size="sm"
      />
    </slot>

    <template #body>
      <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
          <h3>Are you sure you want to export this video?</h3>
          <p class="text-sm text-neutral-500">All media files will be copied to the exports folder.</p>
        </div>

        <USwitch
          v-model="deleteAfterExport"
          label="Delete video after export"
          description="The video is only deleted once every file has been exported."
        />
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
        :label="deleteAfterExport ? 'Export and delete' : 'Export'"
        variant="soft"
        :color="deleteAfterExport ? 'error' : 'primary'"
        loading-auto
        @click.prevent="handle(close)"
      />
    </template>
  </UModal>
</template>
