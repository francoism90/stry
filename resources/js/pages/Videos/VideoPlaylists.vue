<script setup lang="ts">
import PlaylistCard from '@/components/Playlist/PlaylistCard.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import ResourceLayout from '@/layouts/Video/VideoResource.vue'
import type { PlaylistCollection, Video } from '@/types'

interface Props {
  video: Video
  items: PlaylistCollection
}

defineOptions({ layout: [DefaultLayout, ResourceLayout] })

defineProps<Props>()
</script>

<template>
  <InfiniteScroll data="items">
    <UPageGrid class="gap-4">
      <PlaylistCard
        v-for="item in items.data"
        :key="item.id"
        :item
      />
    </UPageGrid>
  </InfiniteScroll>
</template>
