<script setup lang="ts">
import VideoDispatchTranscodeController from '@/actions/Modules/Web/Videos/Controllers/VideoDispatchTranscodeController'
import TranscodeDeleteModal from '@/components/Transcodes/TranscodeDeleteModal.vue'
import TranscodeImportModal from '@/components/Transcodes/TranscodeImportModal.vue'
import ResourceRow from '@/components/Ui/ResourceRow.vue'
import type { Transcode, Video } from '@/types'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  video?: Video
  items?: Transcode[] | undefined
}>()

const stateColors: Record<string, string> = {
  success: 'text-success',
  error: 'text-error',
  warning: 'text-warning',
  info: 'text-info',
  primary: 'text-primary',
}

const description = (item: Transcode): string =>
  [item.encoder, item.file_size, item.error_message].filter(Boolean).join(' · ')

const createTranscode = (): void =>
  void router.post(VideoDispatchTranscodeController.url(props.video!.id), {}, { preserveScroll: true })
</script>

<template>
  <div class="flex flex-col gap-3">
    <div
      v-if="video"
      class="flex items-center gap-2"
    >
      <TranscodeImportModal
        v-if="items?.length"
        :video="video"
      />

      <UButton
        icon="i-lucide-plus"
        label="Create transcode"
        color="neutral"
        variant="outline"
        size="sm"
        @click="createTranscode"
      />
    </div>

    <div
      v-if="items === undefined"
      class="flex flex-col gap-1"
      aria-hidden="true"
    >
      <div
        v-for="i in 3"
        :key="i"
        class="flex items-center gap-3 p-2"
      >
        <USkeleton class="size-10 shrink-0 rounded-full bg-(--glass)" />
        <div class="flex flex-1 flex-col gap-2">
          <USkeleton class="h-3.5 w-1/3 rounded-sm bg-(--glass)" />
          <USkeleton class="h-3 w-1/4 rounded-sm bg-(--glass)" />
        </div>
      </div>
    </div>

    <ul
      v-else-if="items.length"
      class="flex flex-col gap-1"
    >
      <ResourceRow
        v-for="item in items"
        :key="item.id"
        :title="video ? item.id : (item.resource?.label ?? item.id)"
        :description="description(item)"
      >
        <template #leading>
          <span
            class="grid size-10 shrink-0 place-items-center rounded-full bg-(--glass-strong)"
            :class="stateColors[item.state.color ?? ''] ?? 'text-muted'"
            aria-hidden="true"
          >
            <UIcon
              :name="item.processing ? 'i-lucide-loader-circle' : item.state.icon || 'i-lucide-film'"
              class="size-5"
              :class="{ 'animate-spin': item.processing }"
            />
          </span>
        </template>

        <template #meta>
          <UBadge
            :label="item.state.label"
            :color="item.state.color"
            variant="subtle"
            size="sm"
            class="rounded-full"
          />
        </template>

        <template #actions>
          <TranscodeDeleteModal :item="item">
            <UButton
              icon="i-lucide-trash-2"
              :aria-label="`Delete transcode ${item.id}`"
              color="error"
              variant="ghost"
              size="sm"
              class="rounded-full"
            />
          </TranscodeDeleteModal>
        </template>
      </ResourceRow>
    </ul>

    <UEmpty
      v-else-if="!video"
      icon="i-lucide-film"
      title="No transcodes"
      description="Transcodes appear here once videos have been processed."
      variant="naked"
      class="py-24"
    />
  </div>
</template>
