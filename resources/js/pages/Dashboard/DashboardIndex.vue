<script setup lang="ts">
import DashboardToolbar from '@/components/Dashboard/DashboardToolbar.vue'
import VideoPosts from '@/components/Video/VideoPosts.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { PageView, VideoCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'

interface Props {
  items: VideoCollection
  view: PageView
}

defineOptions({ layout: DashboardLayout })

defineProps<Props>()
</script>

<template>
  <Head title="Library" />

  <UPage>
    <UPageBody>
      <DashboardToolbar />

      <UContainer class="py-4">
        <InfiniteScroll
          items-element="#item-list"
          data="items"
          :buffer="200"
        >
          <VideoPosts
            :items="items"
            :orientation="view"
          />
        </InfiniteScroll>
      </UContainer>
    </UPageBody>
  </UPage>
</template>
