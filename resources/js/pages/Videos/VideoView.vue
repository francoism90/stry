<script setup lang="ts">
import VideoEditModal from '@/components/Videos/VideoEditModal.vue'
import VideoGroupModal from '@/components/Videos/VideoGroupModal.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import VideoPlayer from '@/components/Videos/VideoPlayer.vue'
import VideoTags from '@/components/Videos/VideoTags.vue'
import { useEcho } from '@/composables/echo'
import { useVideo } from '@/composables/video'
import ResourceLayout from '@/layouts/App/ResourceLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { Group, Media, Playlist, QueryFilter, QueryValue, Transcode, Video } from '@/types'
import { Deferred, Head, router, setLayoutProps } from '@inertiajs/vue3'
import type { ButtonProps } from '@nuxt/ui'
import { computed, ref } from 'vue'

const props = defineProps<{
  video: Video
  playlist?: Playlist | undefined
  progress?: number | undefined
  groups?: Group[] | undefined
  media?: Media[] | undefined
  playlists?: Playlist[] | undefined
  transcodes?: Transcode[] | undefined
  queue?: Video[] | undefined
  filter?: QueryFilter
  sort?: QueryValue
  query?: QueryValue
}>()

defineOptions({
  layout: [AppLayout, ResourceLayout],
})

setLayoutProps({
  id: 'play',
  fluid: true,
  filter: props.filter,
  sort: props.sort,
  query: props.query,
})

const isAddModalOpen = ref(false)
const isEditModalOpen = ref(false)

const { toggleLike, toggleSave } = useVideo()
const { privateChannel } = useEcho()

const links = computed<ButtonProps[]>(() => [
  {
    label: 'Edit',
    icon: 'i-lucide-edit',
    onClick: () => void (isEditModalOpen.value = true),
  },
  {
    label: props.video.liked ? 'Unlike' : 'Like',
    icon: props.video.liked ? 'i-lucide-heart' : 'i-lucide-heart-plus',
    onClick: () => toggleLike(props.video),
  },
  {
    label: props.video.saved ? 'Unsave' : 'Save',
    icon: props.video.saved ? 'i-lucide-bookmark' : 'i-lucide-bookmark-plus',
    onClick: () => toggleSave(props.video),
  },
  {
    label: 'Add',
    icon: 'i-lucide-list-plus',
    onClick: () => void (isAddModalOpen.value = true),
  },
])

privateChannel(`videos.${props.video.id}`)
  .listen('.videos.updated', () => router.reload({ only: ['video'] }))
  .listen('.videos.trashed', () => router.visit('/'))
  .listen('.playlist.created', () => router.reload({ only: ['playlist', 'playlists'] }))
  .listen('.playlist.updated', () => router.reload({ only: ['playlist', 'playlists'] }))
  .listen('.playlist.deleted', () => router.reload({ only: ['playlist', 'playlists'] }))
  .listen('.transcode.created', () => router.reload({ only: ['transcodes'] }))
  .listen('.transcode.updated', () => router.reload({ only: ['transcodes'] }))
  .listen('.transcode.deleted', () => router.reload({ only: ['transcodes'] }))
  .listen('.media.created', () => router.reload({ only: ['media'] }))
  .listen('.media.updated', () => router.reload({ only: ['media'] }))
  .listen('.media.deleted', () => router.reload({ only: ['media'] }))
</script>

<template>
  <Head :title="video.title" />

  <UPage class="mt-6">
    <VideoPlayer
      :video="video"
      :playlist="playlist"
      :progress="progress"
    />

    <VideoGroupModal
      v-model:open="isAddModalOpen"
      :video="video"
      :groups="groups"
    />

    <VideoEditModal
      v-model:open="isEditModalOpen"
      :video="video"
      :progress="progress"
      :media="media"
      :playlists="playlists"
      :transcodes="transcodes"
    />

    <UPageHeader
      :title="video.title"
      :links="links"
      :ui="{
        title: 'text-xl wrap-anywhere capitalize sm:text-2xl',
      }"
    >
      <template #description>
        <p
          v-if="video.description?.length"
          v-html="video.description"
          class="mb-2"
        />

        <VideoTags :items="video.tags" />
      </template>
    </UPageHeader>

    <Deferred data="queue">
      <template #fallback>
        <div class="sr-only">Loading queue...</div>
      </template>

      <UPageBody class="space-y-8">
        <UPageFeature
          title="Up next"
          class="mt-6"
        />

        <VideoList :items="queue" />
      </UPageBody>
    </Deferred>
  </UPage>
</template>
