<script setup lang="ts">
import VideoCard from '@/components/Video/VideoCard.vue'
import type { VideoCollection } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'

interface Props {
  items: VideoCollection
}

defineProps<Props>()
</script>

<template>
  <UPageBody>
    <slot />

    <InfiniteScroll
      data="items"
      items-element="#page-grid"
      :buffer="200"
    >
      <UPageGrid
        id="page-grid"
        class="gap-x-4 gap-y-6 py-2"
      >
        <VideoCard
          v-for="item in items.data"
          :key="item.id"
          :item
        />
      </UPageGrid>
    </InfiniteScroll>
  </UPageBody>
</template>
