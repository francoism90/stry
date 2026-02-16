<script setup lang="ts">
import TranscodeImportController from '@/actions/App/Api/Transcodes/Controllers/TranscodeImportController'
import type { Transcode } from '@/types'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  item: Transcode
}>()

const handleImport = async (close: () => void) =>
  router.post(
    TranscodeImportController.url(props.item.id),
    {},
    {
      preserveScroll: true,
      onSuccess: () => close(),
    },
  )
</script>

<template>
  <UModal
    :title="item.id"
    :ui="{ footer: 'justify-end' }"
  >
    <slot>
      <UButton
        icon="i-lucide-import"
        color="neutral"
        variant="ghost"
        size="sm"
      />
    </slot>

    <template #body>
      <div class="flex h-24 flex-col gap-2">
        <h3>Are you sure you want to import this transcode?</h3>
        <p class="text-sm text-neutral-500">This action will import the transcode into the parent model.</p>
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
        label="Import transcode"
        variant="soft"
        color="error"
        loading-auto
        @click.prevent="() => handleImport(close)"
      />
    </template>
  </UModal>
</template>
