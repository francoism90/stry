<script setup lang="ts">
import MediaCard from '@/components/Media/MediaCard.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import ResourceLayout from '@/layouts/Video/VideoResource.vue'
import type { MediaCollection, Video } from '@/types'

interface Props {
  video: Video
  items: MediaCollection
}

defineOptions({ layout: [DefaultLayout, ResourceLayout] })

defineProps<Props>()
</script>

<template>
  <InfiniteScroll data="items">
    <UPageGrid class="gap-4">
      <MediaCard
        v-for="item in items.data"
        :key="item.id"
        :item
      />
    </UPageGrid>
  </InfiniteScroll>
</template>
