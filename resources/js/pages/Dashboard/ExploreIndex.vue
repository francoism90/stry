<script setup lang="ts">
import DashboardToolbar from '@/components/Dashboard/DashboardToolbar.vue'
import TagList from '@/components/Tag/TagList.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { TagCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'

interface Props {
  items: TagCollection
}

defineOptions({ layout: [DefaultLayout, DashboardLayout] })

defineProps<Props>()
</script>

<template>
  <Head title="Library" />

  <UPage>
    <DashboardToolbar default-filter="serie" />

    <UPageBody>
      <UContainer class="py-4">
        <InfiniteScroll
          data="items"
          items-element="#page-grid"
          :buffer="200"
        >
          <TagList :items="items" />
        </InfiniteScroll>
      </UContainer>
    </UPageBody>
  </UPage>
</template>
