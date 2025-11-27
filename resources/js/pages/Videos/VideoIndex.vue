<script setup lang="ts">
import VideoCard from '@/components/Video/VideoCard.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import VideoCollection from '@/layouts/Videos/VideoCollection.vue'
import type { Videos } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'

interface Props {
  items: Videos
}

defineOptions({ layout: [DashboardLayout, VideoCollection] })

defineProps<Props>()
</script>

<template>
  <Head title="Library" />

  <UPage>
    <InfiniteScroll
      data="items"
      items-element="#video-items"
      :buffer="200"
    >
      <UBlogPosts id="video-items">
        <VideoCard
          v-for="item in items?.data"
          :key="item.id"
          :item="item"
        />
      </UBlogPosts>
    </InfiniteScroll>
  </UPage>
</template>
