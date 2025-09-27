<script setup lang="ts">
import MediaCard from '@/components/Media/MediaCard.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import ResourceLayout from '@/layouts/Video/VideoResource.vue'
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
    :buffer="200"
  >
    <UPageGrid class="gap-3">
      <MediaCard
        v-for="item in items.data"
        :key="item.id"
        :item
      />
    </UPageGrid>
  </InfiniteScroll>
</template>
