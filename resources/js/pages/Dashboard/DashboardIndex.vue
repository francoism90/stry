<script setup lang="ts">
import DashboardToolbar from '@/components/Dashboard/DashboardToolbar.vue'
import VideoCard from '@/components/Video/VideoCard.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { VideoCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'

interface Props {
  items: VideoCollection
}

defineOptions({ layout: [DefaultLayout, DashboardLayout] })

defineProps<Props>()
</script>

<template>
  <Head title="Library" />

  <UPage>
    <DashboardToolbar />

    <UPageBody>
      <UContainer class="py-4">
        <InfiniteScroll
          data="items"
          items-element="#page-grid"
          :buffer="200"
        >
          <UBlogPosts
            id="page-grid"
            orientation="vertical"
          >
            <VideoCard
              v-for="(item, index) in items.data"
              :key="index"
              :item="item"
            />
          </UBlogPosts>
        </InfiniteScroll>
      </UContainer>
    </UPageBody>
  </UPage>
</template>
