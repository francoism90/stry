<script setup lang="ts">
import MediaDeleteModal from '@/components/Media/MediaDeleteModal.vue'
import { useMedia } from '@/composables/media'
import VideoLayout from '@/layouts/App/VideoLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { MediaCollection, Video } from '@/types'
import { Head, InfiniteScroll, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'

const props = defineProps<{
  video: Video
  items: MediaCollection
}>()

defineOptions({ layout: [DefaultLayout, VideoLayout] })

const { getStreamInfo } = useMedia()

useEcho<Video>(`videos.${props.video.id}`, '.media.created', () => router.reload({ only: ['items'], reset: ['items'] }))
useEcho<Video>(`videos.${props.video.id}`, '.media.updated', () => router.reload({ only: ['items'], reset: ['items'] }))
useEcho<Video>(`videos.${props.video.id}`, '.media.deleted', () => router.reload({ only: ['items'], reset: ['items'] }))
</script>

<template>
  <Head :title="`${video.title} - Media`" />

  <UPageBody>
    <InfiniteScroll
      data="items"
      items-element="#infinite-items"
      :buffer="200"
    >
      <UPageList
        id="infinite-items"
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
              :name="item.file_name"
              :description="getStreamInfo(item).join(' · ')"
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
  </UPageBody>
</template>
