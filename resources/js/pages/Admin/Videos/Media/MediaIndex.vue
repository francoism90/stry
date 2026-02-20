<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Media/Controllers/MediaController'
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

const { getStreamInfo } = useMedia()
</script>

<template>
  <Head :title="`${video.title} - Media`" />

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
            :name="item.file_name"
            :description="getStreamInfo(item).join(' • ')"
            :avatar="{
              alt: item.file_name,
              loading: 'lazy',
              decoding: 'async',
              class: 'rounded-sm size-12 me-1',
            }"
          />

          <div class="z-10 flex items-center gap-2">
            <MediaDeleteModal :item="item" />
          </div>
        </div>
      </UPageCard>
    </UPageList>
  </InfiniteScroll>
</template>
