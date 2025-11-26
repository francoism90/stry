<script setup lang="ts">
import VideoCard from '@/components/Video/VideoCard.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { PageView, VideoCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'

interface Props {
  items: VideoCollection
  view?: PageView
}

defineOptions({ layout: DashboardLayout })

defineProps<Props>()
</script>

<template>
  <Head title="Library" />

  <UPage>
    <UPageHeader
      title="Videos"
      description="Browse and manage your video library."
    />

    <UPageBody>
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
    </UPageBody>
  </UPage>
</template>
