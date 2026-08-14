<script setup lang="ts">
import VideoCard from '@/components/Videos/VideoCard.vue'
import type { Video } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'

defineProps<{
  items: Video[] | undefined
}>()
</script>

<template>
  <InfiniteScroll
    data="items"
    class="flex flex-col"
    items-element="#video-index"
    :buffer="200"
  >
    <div id="videos-header" />

    <UBlogPosts
      id="video-body"
      class="grid grid-cols-1 gap-4 gap-y-6 sm:grid-cols-3 lg:gap-y-8 xl:grid-cols-4"
    >
      <VideoCard
        v-for="(item, index) in items"
        :key="item.id"
        :item="item"
        :index="index"
      />
    </UBlogPosts>

    <div id="videos-footer" />
  </InfiniteScroll>
</template>
