<script setup lang="ts">
import PlaylistCard from '@/components/Playlist/PlaylistCard.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import ResourceLayout from '@/layouts/Video/VideoLayout.vue'
import type { PlaylistCollection, Video } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'

interface Props {
  video: Video
  items: PlaylistCollection
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
      <PlaylistCard
        v-for="item in items.data"
        :key="item.id"
        :item
      />
    </UPageGrid>
  </InfiniteScroll>
</template>
