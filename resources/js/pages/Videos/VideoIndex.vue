<script setup lang="ts">
import VideoList from '@/components/Videos/VideoList.vue'
import ContentLayout from '@/layouts/App/ContentLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { VideoCollection } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'

defineProps<{
  items: VideoCollection
}>()

defineOptions({
  layout: [
    [AppLayout, { title: 'Videos' }],
    [ContentLayout, { id: 'videos.index' }],
  ],
})
</script>

<template>
  <UPage>
    <InfiniteScroll
      data="items"
      items-element="#video-index"
      :buffer="200"
    >
      <VideoList
        id="video-index"
        :items="items?.data"
      />
    </InfiniteScroll>
  </UPage>
</template>
