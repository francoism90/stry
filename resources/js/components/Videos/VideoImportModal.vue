<script setup lang="ts">
import { useVideos } from '@/composables/videos'
import { router, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'

const isOpen = defineModel<boolean>({ default: false })
const page = usePage()
const toast = useToast()

const importing = ref(false)
const { importVideos } = useVideos()

const handleImport = () => {
  importing.value = true

  importVideos(() => {
    importing.value = false
    isOpen.value = false
  })
}

router.on('flash', (event) => {
  if (event.detail.flash) {
    toast.add({
      title: 'Import videos',
      description: event.detail.flash.message as string,
      icon: 'i-lucide-info',
      color: 'success',
    })
  }
})
</script>

<template>
  <UModal
    v-model="isOpen"
    title="Import Videos"
    :ui="{ footer: 'justify-end' }"
  >
    <UButton
      label="Import videos"
      color="primary"
      variant="soft"
      icon="i-lucide-upload"
    />

    <template #body>
      <div
        v-if="page.flash"
        class="toast"
      >
        {{ page.flash.message }}
      </div>

      <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
          <h3 class="text-sm font-medium">Import videos from disk</h3>
          <p class="text-sm text-neutral-500">
            This will scan the import directory for video files and queue them for import. The import process runs in
            the background and may take some time depending on the number of files.
          </p>
        </div>

        <div class="rounded-lg bg-amber-50 p-3 dark:bg-amber-950/20">
          <div class="flex gap-2">
            <UIcon
              name="i-lucide-alert-triangle"
              class="size-5 text-amber-600 dark:text-amber-400"
            />
            <div class="flex flex-col gap-1 text-sm">
              <p class="font-medium text-amber-900 dark:text-amber-200">Note</p>
              <p class="text-amber-800 dark:text-amber-300">
                Supported formats: MP4, MKV, AVI, MOV, WMV, FLV, WebM, M4V
              </p>
            </div>
          </div>
        </div>
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
        label="Start import"
        variant="soft"
        color="primary"
        :loading="importing"
        @click.prevent="handleImport"
      />
    </template>
  </UModal>
</template>
