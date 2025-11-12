<script setup lang="ts">
import DashboardToolbar from '@/components/Dashboard/DashboardToolbar.vue'
import VideoItems from '@/components/Video/VideoItems.vue'
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
    <UPageBody>
      <DashboardToolbar default-filter="recommended" />

      <UContainer class="py-4">
        <InfiniteScroll
          items-element="#feed-items"
          data="items"
          :buffer="200"
        >
          <VideoItems
            id="feed-items"
            :items="items"
            :orientation="view"
          />
        </InfiniteScroll>
      </UContainer>
    </UPageBody>
  </UPage>
</template>
