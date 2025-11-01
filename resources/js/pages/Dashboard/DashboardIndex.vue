<script setup lang="ts">
import DashboardToolbar from '@/components/Dashboard/DashboardToolbar.vue'
import VideoPosts from '@/components/Video/VideoPosts.vue'
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

  <UPage>
    <UPageBody>
      <DashboardToolbar default-filter="recommended" />

      <UContainer class="py-4">
        <InfiniteScroll
          data="items"
          items-element="#item-list"
          :buffer="200"
        >
          <VideoPosts :items="items" />
        </InfiniteScroll>
      </UContainer>
    </UPageBody>
  </UPage>
</template>
