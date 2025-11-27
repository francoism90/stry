<script setup lang="ts">
import VideoCard from '@/components/Video/VideoCard.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { VideoCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'

interface Props {
  items: VideoCollection
}

defineOptions({ layout: DashboardLayout })

defineProps<Props>()
</script>

<template>
  <Head title="Library" />

  <UDashboardPanel id="videos">
    <template #header>
      <UDashboardNavbar title="Videos">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>

        <template #right>
          <!-- <CustomersAddModal /> -->
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
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
    </template>
  </UDashboardPanel>
</template>
