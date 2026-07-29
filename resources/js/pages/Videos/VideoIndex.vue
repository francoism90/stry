<script setup lang="ts">
import VideoList from '@/components/Videos/VideoList.vue'
import type { VideoCollection } from '@/types'
import { InfiniteScroll, setLayoutProps } from '@inertiajs/vue3'

defineProps<{
  items: VideoCollection
}>()

setLayoutProps({
  id: 'videos.index',
  showFilter: true,
})
</script>

<template>
  <UPage>
    <InfiniteScroll
      data="items"
      items-element="#infinite-items"
      :buffer="200"
    >
      <VideoList
        id="infinite-items"
        :items="items?.data"
      />
    </InfiniteScroll>
  </UPage>
</template>
