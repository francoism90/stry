<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Videos/Controllers/VideoMediaController'
import MediaDeleteModal from '@/components/Media/MediaDeleteModal.vue'
import VideoLayout from '@/layouts/Admin/VideoLayout.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { MediaCollection, Video } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'

defineProps<{
  video: Video
  items: MediaCollection
}>()

defineOptions({ layout: [DashboardLayout, VideoLayout] })
</script>

<template>
  <Head :title="`${video.title} - Media`" />

  <InfiniteScroll
    data="items"
    start-element="#video-media-header"
    items-element="#video-media-list"
    :buffer="200"
  >
    <UPageList
      id="video-media-list"
      divide
    >
      <UPageCard
        v-for="item in items?.data"
        :key="item.id"
        variant="naked"
        class="py-4 first:pt-0 last:pb-0"
      >
        <div class="flex items-center justify-between">
          <UUser
            :name="item.name"
            :description="`${item.mime_type} • ${item.file_size}`"
            :avatar="{
              alt: item.name,
              class: 'rounded-sm size-14 me-1',
            }"
          />

          <div class="flex gap-2">
            <UButton
              icon="i-lucide-pencil"
              color="secondary"
              variant="ghost"
              size="sm"
              :to="edit.url({ video: video.id, media: item.id })"
            />

            <MediaDeleteModal
              :video="video"
              :item="item"
            >
              <UButton
                icon="i-lucide-trash"
                color="error"
                variant="ghost"
                size="sm"
              />
            </MediaDeleteModal>
          </div>
        </div>
      </UPageCard>
    </UPageList>
  </InfiniteScroll>
</template>
