<script setup lang="ts">
import AppHeader from '@/components/Ui/AppHeader.vue'
import VideoFilters from '@/components/Videos/VideoFilters.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import type { FilterOption, Tag, VideoCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

const props = defineProps<{
  items: VideoCollection
  orders: SelectMenuItem[]
  filter: FilterOption
  tag?: Tag | undefined
  order?: string | undefined
}>()
</script>

<template>
  <Head :title="filter.label" />

  <UDashboardPanel id="feed">
    <template #header>
      <AppHeader />

      <VideoFilters
        :orders="orders"
        :tag="tag"
        :order="order"
      />
    </template>

    <template #body>
      <UPage>
        <InfiniteScroll
          data="items"
          :buffer="200"
        >
          <VideoList :items="items?.data" />
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
