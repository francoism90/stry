<script setup lang="ts">
import DashboardToolbar from '@/components/Dashboard/DashboardToolbar.vue'
import VideoPosts from '@/components/Video/VideoPosts.vue'
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
    <DashboardToolbar default-filter="recommended" />

    <UPageBody>
      <UContainer class="py-4">
        <InfiniteScroll
          data="items"
          items-element="#feed-list"
          :buffer="200"
        >
          <VideoPosts
            id="feed-list"
            :items="items"
          />
        </InfiniteScroll>
      </UContainer>
    </UPageBody>
  </UPage>
</template>
