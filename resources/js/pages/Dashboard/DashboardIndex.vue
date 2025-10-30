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

    <UPageBody>
      <UContainer class="py-4">
        <InfiniteScroll
          data="items"
          items-element="#page-grid"
          :buffer="200"
        >
          <UBlogPosts
            orientation="vertical"
            id="page-grid"
            class="gap-4 lg:gap-y-8"
          >
            <UBlogPost
              v-for="(item, index) in items.data"
              :key="index"
              :title="item.name"
              :description="item.summary"
              :image="item.thumb"
              :date="item.published_at ?? item.created_at"
              :badge="{
                label: item.timestamp,
                color: 'primary',
                variant: 'solid',
              }"
            />
          </UBlogPosts>
        </InfiniteScroll>
      </UContainer>
    </UPageBody>
  </UPage>
</template>
