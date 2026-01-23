<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Videos/Controllers/VideoMediaController'
import MediaDeleteModal from '@/components/Media/MediaDeleteModal.vue'
import { useMedia } from '@/composables/media'
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
          <div class="flex flex-1 flex-col gap-1">
            <div class="flex items-center gap-2">
              <p class="font-semibold">{{ item.name }}</p>
            </div>

            <p class="text-sm text-gray-500 dark:text-gray-400">{{ item.mime_type }} • {{ item.file_size }}</p>

            <div
              v-if="item.custom_properties"
              class="flex items-center gap-1.5 pt-1"
            >
              <UBadge
                v-for="(badge, index) in useMedia(item).getStreamInfo()"
                :key="index"
                :color="badge.color"
                variant="subtle"
                size="xs"
              >
                {{ badge.label }}
              </UBadge>
            </div>
          </div>

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
