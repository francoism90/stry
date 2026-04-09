<script setup lang="ts">
import AppHeader from '@/components/Ui/AppHeader.vue'
import VideoFilters from '@/components/Videos/VideoFilters.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import type { VideoCollection } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

defineProps<{
  items: VideoCollection
  sorters: SelectMenuItem[]
  sort?: string
}>()
</script>

<template>
  <UDashboardPanel id="videos">
    <template #header>
      <AppHeader />
    </template>

    <template #body>
      <UPage>
        <UDashboardToolbar>
          <template #left>
            <VideoFilters
              :results="Boolean(items?.data?.length)"
              :sorters="sorters"
              :sort="sort"
            />
          </template>
        </UDashboardToolbar>

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
