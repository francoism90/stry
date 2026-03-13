<script setup lang="ts">
import AppHeader from '@/components/Ui/AppHeader.vue'
import VideoFilters from '@/components/Videos/VideoFilters.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import type { VideoCollection } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

defineProps<{
  items: VideoCollection
  orders: SelectMenuItem[]
  order?: string | undefined
}>()
</script>

<template>
  <UDashboardPanel id="feed">
    <template #header>
      <AppHeader />

      <VideoFilters
        :orders="orders"
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
