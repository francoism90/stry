<script setup lang="ts">
import { edit } from '@/actions/App/Web/Transcodes/Controllers/TranscodeController'
import TranscodeDeleteModal from '@/components/Transcodes/TranscodeDeleteModal.vue'
import TranscodeImportModal from '@/components/Transcodes/TranscodeImportModal.vue'
import VideoLayout from '@/layouts/App/VideoLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { TranscodeCollection, Video } from '@/types'
import { Head, InfiniteScroll, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'

defineOptions({ layout: [DefaultLayout, VideoLayout] })

const props = defineProps<{
  video: Video
  items: TranscodeCollection
}>()

useEcho<Video>(`videos.${props.video.id}`, '.transcode.created', () => router.reload({ only: ['items'] }))
useEcho<Video>(`videos.${props.video.id}`, '.transcode.updated', () => router.reload({ only: ['items'] }))
useEcho<Video>(`videos.${props.video.id}`, '.transcode.deleted', () => router.reload({ only: ['items'] }))
</script>

<template>
  <Head :title="`${video.title} - Transcodes`" />

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
              :description="`${item.state.label} • ${item.file_size}`"
              :avatar="{
                alt: item.id,
                loading: 'lazy',
                decoding: 'async',
                class: 'rounded-sm size-12 me-1',
              }"
            />

            <div class="z-10 flex items-center gap-2">
              <TranscodeDeleteModal :item="item" />
              <TranscodeImportModal :item="item" />
            </div>
          </div>
        </UPageCard>
      </UPageList>
    </InfiniteScroll>
  </UPage>
</template>
