<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Videos/Controllers/VideoPlaylistController'
import VideoLayout from '@/layouts/Admin/VideoLayout.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { PlaylistCollection, Video } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'

defineProps<{
  video: Video
  items: PlaylistCollection
}>()

defineOptions({ layout: [DashboardLayout, VideoLayout] })
</script>

<template>
  <Head :title="`${video.title} - Playlists`" />

  <InfiniteScroll
    data="items"
    start-element="#video-playlist-header"
    items-element="#video-playlist-list"
    :buffer="200"
  >
    <UPageList
      id="video-playlist-list"
      divide
    >
      <UPageCard
        v-for="item in items?.data"
        :key="item.id"
        variant="naked"
        class="py-4 first:pt-0 last:pb-0"
      >
        <div class="flex items-center justify-between">
          <div class="flex flex-1 flex-col gap-1">
            <div class="flex items-center gap-2">
              <p class="font-semibold">{{ item.type }}</p>
              <UBadge
                :label="item.state.name"
                :color="item.valid ? 'success' : item.expired ? 'warning' : item.failed ? 'error' : 'neutral'"
              />
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Created {{ item.created_at }}</p>
          </div>

          <div class="flex gap-2">
            <UButton
              icon="i-lucide-pencil"
              color="secondary"
              variant="ghost"
              size="sm"
              :to="edit.url({ video: video.id, playlist: item.id })"
            />

            <UButton
              icon="i-lucide-trash"
              color="error"
              variant="ghost"
              size="sm"
            />
          </div>
        </div>
      </UPageCard>
    </UPageList>
  </InfiniteScroll>
</template>
