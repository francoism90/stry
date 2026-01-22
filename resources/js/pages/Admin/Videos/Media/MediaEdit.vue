<script setup lang="ts">
import { update } from '@/actions/App/Admin/Videos/Controllers/VideoMediaController'
import MediaDeleteModal from '@/components/Media/MediaDeleteModal.vue'
import { useMedia } from '@/composables/media'
import VideoLayout from '@/layouts/Admin/VideoLayout.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { Media, Video } from '@/types'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  video: Video
  media: Media
}>()

defineOptions({ layout: [DashboardLayout, VideoLayout] })

const toast = useToast()

const form = useForm('put', update.url([props.video.id, props.media.id]), {
  name: props.media.name,
})

const { startConversion, replaceWithTranscode, getStateColor } = useMedia(props.video, props.media)

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
</script>

<template>
  <UForm
    id="general"
    :state="form"
    class="mx-auto flex w-full flex-col gap-6 sm:gap-9 lg:max-w-3xl lg:py-3"
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
      title="AV1 Conversion"
      description="Convert this media to AV1 format for better compression and quality."
      variant="subtle"
    >
      <div class="space-y-4">
        <div
          v-if="media.transcodes?.length"
          class="space-y-3"
        >
          <div
            v-for="transcode in media.transcodes"
            :key="transcode.id"
            class="flex items-center justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-800"
          >
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <UBadge :color="getStateColor(transcode.state)">
                  {{ transcode.state }}
                </UBadge>
                <span class="text-sm text-gray-600 dark:text-gray-400">
                  {{ transcode.codec.toUpperCase() }}
                </span>
              </div>
              <div
                v-if="transcode.state === 'processing'"
                class="text-sm text-gray-500 dark:text-gray-400"
              >
                Progress: {{ transcode.progress }}%
              </div>
              <div
                v-if="transcode.error_message"
                class="text-error text-sm"
              >
                {{ transcode.error_message }}
              </div>
            </div>

            <UButton
              v-if="transcode.state === 'completed'"
              label="Replace Original"
              color="primary"
              variant="soft"
              icon="i-lucide-arrow-right-left"
              @click="replaceWithTranscode(Number(transcode.id))"
            />
          </div>
        </div>

        <UButton
          v-else
          label="Start AV1 Conversion"
          color="primary"
          variant="soft"
          icon="i-lucide-play"
          @click="startConversion"
        />
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
