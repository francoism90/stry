<script setup lang="ts">
import { update } from '@/actions/App/Admin/Videos/Controllers/VideoMediaController'
import MediaDeleteModal from '@/components/Media/MediaDeleteModal.vue'
import { useMedia } from '@/composables/media'
import VideoLayout from '@/layouts/Admin/VideoLayout.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { Media, Transcode, Video } from '@/types'
import { router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  video: Video
  media: Media
  transcodes: Transcode[]
}>()

defineOptions({ layout: [DashboardLayout, VideoLayout] })

const toast = useToast()

const form = useForm('put', update.url([props.video.id, props.media.id]), {
  name: props.media.name,
})

const { startConversion, addTranscodes, getStreamInfo, getAv1Stream } = useMedia(props.media)

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
    onSuccess: () =>
      toast.add({
        title: 'Success',
        description: 'The media has been updated.',
        icon: 'i-lucide-check',
        color: 'success',
      }),
  })

useEcho<Video>(`videos.${props.video.id}`, '.transcode.updated', () => router.reload({ only: ['transcodes'] }))
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
      :title="media.name"
      :description="`${media.mime_type} • ${media.file_size}`"
      variant="naked"
      orientation="horizontal"
    >
      <div class="ms-auto">
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
      title="AV1 Conversion"
      description="Convert this media to AV1 format for better compression."
      variant="subtle"
    >
      <div class="space-y-4">
        <UPageList
          v-if="transcodes?.length"
          divide
        >
          <UPageCard
            v-for="transcode in transcodes"
            :key="transcode.id"
            variant="naked"
            class="py-4 first:pt-0 last:pb-0"
          >
            <div class="flex items-center justify-between">
              <div class="space-y-1">
                <div class="flex items-center gap-2">
                  <UBadge :color="transcode.state.color">
                    {{ transcode.state.name }}
                  </UBadge>

                  <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ transcode.encoder }}
                  </span>

                  <span
                    v-if="transcode.file_size && transcode.completed"
                    class="text-sm text-gray-600 dark:text-gray-400"
                  >
                    {{ transcode.file_size_human }}

                    <span class="text-success">
                      ({{ Math.round(((media.size - transcode.file_size) / media.size) * 100) }}% smaller)
                    </span>
                  </span>
                </div>

                <div
                  v-if="transcode.error_message"
                  class="text-error line-clamp-2 text-sm"
                >
                  {{ transcode.error_message }}
                </div>
              </div>
            </div>
          </UPageCard>
        </UPageList>

        <div class="flex items-center gap-3">
          <UButton
            v-if="transcodes?.some((t) => t.completed)"
            label="Add Transcodes"
            color="primary"
            variant="soft"
            icon="i-lucide-plus"
            @click="addTranscodes"
          />

          <UButton
            v-else-if="getAv1Stream() === null && !transcodes?.some((t) => t.processing || t.pending)"
            label="Perform AV1 Conversion"
            color="primary"
            variant="soft"
            @click="startConversion"
          />

          <div
            v-if="getAv1Stream() !== null || transcodes?.some((t) => t.completed)"
            class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
          >
            <UIcon
              name="i-lucide-check-circle"
              class="size-4"
            />

            <span>This media is already in AV1 format</span>
          </div>
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
        />
      </template>
    </UPageCard>
  </UForm>
</template>
