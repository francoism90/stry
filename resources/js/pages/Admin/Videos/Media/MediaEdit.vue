<script setup lang="ts">
import { update } from '@/actions/App/Admin/Videos/Controllers/VideoMediaController'
import MediaDeleteModal from '@/components/Media/MediaDeleteModal.vue'
import { useMedia } from '@/composables/media'
import VideoLayout from '@/layouts/Admin/VideoLayout.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { Media, Video } from '@/types'
import { router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import { useForm } from 'laravel-precognition-vue-inertia'
import { computed } from 'vue'

const props = defineProps<{
  video: Video
  media: Media
}>()

defineOptions({ layout: [DashboardLayout, VideoLayout] })

const form = useForm('put', update.url([props.video.id, props.media.id]), {
  name: props.media.name,
  custom_properties: props.media.custom_properties || {},
})

const { getStreamInfo } = useMedia(props.media)

const customProperties = computed({
  get: () => JSON.stringify(form.custom_properties),
  set: (val) => {
    try {
      form.custom_properties = JSON.parse(val)
    } catch {}
  },
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
  })

useEcho<Video>(`videos.${props.video.id}`, '.media.updated', () => router.reload({ only: ['media'] }))
</script>

<template>
  <UForm
    id="general"
    :state="form"
    class="mx-auto flex w-full flex-col gap-6 sm:gap-9 lg:max-w-4xl lg:py-3"
    loading-auto
    @submit="onSubmit"
  >
    <UPageCard
      title="Media Details"
      description="General information about the media asset"
      variant="naked"
      orientation="horizontal"
    >
      <div class="flex items-center gap-2 lg:ms-auto">
        <UButton
          form="general"
          label="Save changes"
          type="submit"
          color="primary"
          variant="soft"
          loading-auto
        />
      </div>
    </UPageCard>

    <UPageCard variant="subtle">
      <UFormField
        label="Name"
        required
        :error="form.errors.name"
      >
        <UInput
          v-model="form.name"
          :model-modifiers="{ string: true, trim: true }"
          autofocus
        />
      </UFormField>

      <UFormField
        label="Custom Properties (JSON)"
        :error="form.errors.custom_properties"
      >
        <UTextarea
          v-model="customProperties"
          :model-modifiers="{ string: true, trim: true }"
          :rows="8"
          placeholder='{"key": "value"}'
          class="w-full font-mono"
        />
      </UFormField>
    </UPageCard>

    <UPageCard
      v-if="getStreamInfo().length"
      title="Stream Information"
      description="Technical details about the media file."
      variant="subtle"
    >
      <div class="flex items-center gap-2">
        <UBadge
          v-for="(badge, index) in getStreamInfo()"
          :key="index"
          :color="badge.color"
          variant="subtle"
        >
          {{ badge.label }}
        </UBadge>
      </div>
    </UPageCard>

    <UPageCard
      title="Media Attributes"
      description="File and storage details for this media."
      variant="subtle"
    >
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <div class="text-xs text-gray-500 dark:text-gray-400">File Name</div>
          <div class="font-mono text-sm">{{ media.file_name }}</div>
        </div>
        <div>
          <div class="text-xs text-gray-500 dark:text-gray-400">File Size</div>
          <div class="font-mono text-sm">{{ media.file_size }}</div>
        </div>
        <div>
          <div class="text-xs text-gray-500 dark:text-gray-400">MIME Type</div>
          <div class="font-mono text-sm">{{ media.mime_type }}</div>
        </div>
        <div>
          <div class="text-xs text-gray-500 dark:text-gray-400">Collection</div>
          <div class="font-mono text-sm">{{ media.collection_name }}</div>
        </div>
        <div>
          <div class="text-xs text-gray-500 dark:text-gray-400">Disk</div>
          <div class="font-mono text-sm">{{ media.disk }}</div>
        </div>
        <div>
          <div class="text-xs text-gray-500 dark:text-gray-400">Conversions Disk</div>
          <div class="font-mono text-sm">{{ media.conversions_disk }}</div>
        </div>
      </div>
    </UPageCard>

    <UPageCard
      title="Delete Media"
      description="This will delete the media file. There is no going back. Please be certain."
      class="from-error/10 to-default bg-linear-to-tl from-5%"
    >
      <template #footer>
        <MediaDeleteModal
          :video="video"
          :item="media"
        >
          <UButton
            label="Delete media"
            color="error"
            variant="soft"
          />
        </MediaDeleteModal>
      </template>
    </UPageCard>
  </UForm>
</template>
