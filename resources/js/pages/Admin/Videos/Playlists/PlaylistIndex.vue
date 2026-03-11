<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Playlists/Controllers/PlaylistController'
import PlaylistDeleteModal from '@/components/Playlists/PlaylistDeleteModal.vue'
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

  <UPage>
    <InfiniteScroll
      data="items"
      :buffer="200"
    >
      <UPageList divide>
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
              :description="`${item.state.label} • ${item.type}`"
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
  </UPage>
</template>
