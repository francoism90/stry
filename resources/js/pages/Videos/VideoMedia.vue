<script setup lang="ts">
import MediaCard from '@/components/Media/MediaCard.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import ResourceLayout from '@/layouts/Video/VideoLayout.vue'
import type { MediaCollection, Video } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'

interface Props {
  video: Video
  items: MediaCollection
}

defineOptions({ layout: [DefaultLayout, ResourceLayout] })

defineProps<Props>()
</script>

<template>
  <InfiniteScroll
    data="items"
    items-element="#page-grid"
    :buffer="200"
  >
    <UPageGrid
      id="page-grid"
      class="gap-4"
    >
      <MediaCard
        v-for="item in items.data"
        :key="item.id"
        :item
      />
    </UPageGrid>
  </InfiniteScroll>
</template>
