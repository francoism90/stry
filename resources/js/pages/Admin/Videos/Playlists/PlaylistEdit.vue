<script setup lang="ts">
import { update } from '@/actions/App/Admin/Videos/Controllers/VideoPlaylistController'
import PlaylistDeleteModal from '@/components/Playlist/PlaylistDeleteModal.vue'
import VideoLayout from '@/layouts/Admin/VideoLayout.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { Playlist, Video } from '@/types'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  video: Video
  playlist: Playlist
}>()

defineOptions({ layout: [DashboardLayout, VideoLayout] })

const toast = useToast()

const form = useForm('put', update.url([props.video.id, props.playlist.id]), {
  type: props.playlist.type,
  expires_at: props.playlist.expires_at,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
    onSuccess: () =>
      toast.add({
        title: 'Success',
        description: 'The playlist has been updated.',
        icon: 'i-lucide-check',
        color: 'success',
      }),
  })
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
      title="Playlist Details"
      description="General information about the playlist"
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

    <UPageCard
      title="Details"
      variant="subtle"
    >
      <div class="grid gap-4 sm:grid-cols-2">
        <UFormField
          label="Type"
          :error="form.errors.type"
        >
          <UInput
            v-model="form.type"
            :model-modifiers="{ string: true, trim: true }"
          />
        </UFormField>

        <UFormField
          label="Expires"
          :error="form.errors.expires_at"
        >
          <UInput
            v-model="form.expires_at"
            :model-modifiers="{ nullable: true, string: true, trim: true }"
            placeholder="YYYY-MM-DD HH:mm:ss"
          />
        </UFormField>
      </div>
    </UPageCard>

    <UPageCard
      title="Delete Playlist"
      description="This will delete the playlist. There is no going back. Please be certain."
      class="from-error/10 to-default bg-linear-to-tl from-5%"
    >
      <template #footer>
        <PlaylistDeleteModal
          :video="video"
          :item="playlist"
        />
      </template>
    </UPageCard>
  </UForm>
</template>
