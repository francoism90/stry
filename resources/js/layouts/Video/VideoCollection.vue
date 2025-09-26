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

    <InfiniteScroll data="items">
      <UPageGrid class="gap-4">
        <VideoCard
          v-for="item in items.data"
          :key="item.id"
          :item
        />
      </UPageGrid>
    </InfiniteScroll>
  </UPageBody>
</template>
