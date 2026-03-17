<script setup lang="ts">
import { edit } from '@/actions/App/Web/Playlists/Controllers/PlaylistController'
import { create } from '@/actions/App/Web/Videos/Controllers/VideoPlaylistController'
import PlaylistDeleteModal from '@/components/Playlists/PlaylistDeleteModal.vue'
import VideoLayout from '@/layouts/App/VideoLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { PlaylistCollection, Video } from '@/types'
import { Head, InfiniteScroll, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'

defineOptions({ layout: [DefaultLayout, VideoLayout] })

const props = defineProps<{
  video: Video
  items: PlaylistCollection
}>()

useEcho<Video>(`videos.${props.video.id}`, '.playlist.created', () =>
  router.reload({ only: ['items'], reset: ['items'] }),
)
useEcho<Video>(`videos.${props.video.id}`, '.playlist.updated', () =>
  router.reload({ only: ['items'], reset: ['items'] }),
)
useEcho<Video>(`videos.${props.video.id}`, '.playlist.deleted', () =>
  router.reload({ only: ['items'], reset: ['items'] }),
)
</script>

<template>
  <Head :title="`${video.title} - Playlists`" />

  <UPageBody>
    <InfiniteScroll
      data="items"
      :buffer="200"
    >
      <UEmpty
        v-if="!items?.data?.length"
        icon="i-lucide-list-video"
        title="No playlists"
        description="Create a playlist to enable streaming for this video."
        :actions="[{ label: 'Create playlist', icon: 'i-lucide-plus', to: create.url(video.id) }]"
      />

      <UPageList
        v-else
        divide
      >
        <UPageCard
          v-for="item in items?.data"
          :key="item.id"
          :to="edit.url(item.id)"
          variant="naked"
          class="py-4 first:pt-0 last:pb-0"
        >
          <div class="flex items-center justify-between">
            <UUser
              :name="item.id"
              :description="`${item.state.label} · ${item.type}`"
              :avatar="{
                alt: item.id,
                loading: 'lazy',
                decoding: 'async',
                class: 'rounded-sm size-12 me-1',
              }"
            />

            <div class="z-10 flex items-center gap-2">
              <PlaylistDeleteModal :item="item" />
            </div>
          </div>
        </UPageCard>
      </UPageList>
    </InfiniteScroll>
  </UPageBody>
</template>
