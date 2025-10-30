<script setup lang="ts">
import DashboardToolbar from '@/components/Dashboard/DashboardToolbar.vue'
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

    <InfiniteScroll
      data="items"
      items-element="#page-grid"
      :buffer="200"
    >
      <UPageGrid
        id="page-grid"
        class="gap-x-4 gap-y-6 py-2"
      >
        <div
          v-for="item in items.data"
          :key="item.id"
        >
          {{ item.id }} - {{ item.duration }}
        </div>
      </UPageGrid>
    </InfiniteScroll>
  </UPage>
</template>
