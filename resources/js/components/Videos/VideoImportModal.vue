<script setup lang="ts">
import VideoImportController from '@/actions/App/Web/Videos/Controllers/VideoImportController'
import { router } from '@inertiajs/vue3'

const handle = async (close: () => void) =>
  router.post(
    VideoImportController.url(),
    {},
    {
      preserveScroll: true,
      onSuccess: () => close(),
    },
  )
</script>

<template>
  <UModal
    title="Import Videos"
    :ui="{ footer: 'justify-end' }"
  >
    <UButton
      label="Import videos"
      color="neutral"
      variant="soft"
      icon="i-lucide-upload"
    />

    <template #body>
      <div class="flex flex-col gap-2">
        <p>This will import all videos from the specified directory.</p>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Supported formats: MP4, MKV, AVI, MOV, WMV, FLV, WebM, M4V
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
        label="Import videos"
        variant="soft"
        color="primary"
        icon="i-lucide-upload"
        loading-auto
        @click.prevent="handle(close)"
      />
    </template>
  </UModal>
</template>
