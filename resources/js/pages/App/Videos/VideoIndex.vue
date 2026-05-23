<script setup lang="ts">
import AppHeader from '@/components/Ui/AppHeader.vue'
import VideoFilterBar from '@/components/Videos/VideoFilterBar.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import type { VideoCollection, VideoFilters } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

defineProps<{
  items: VideoCollection
  filters: VideoFilters
  sorters: SelectMenuItem[]
  scopes: SelectMenuItem[]
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
            <VideoFilterBar
              :results="Boolean(items?.data?.length)"
              :sorters="sorters"
              :scopes="scopes"
              :sort="sort"
              :filters="filters"
            />
          </template>
        </UDashboardToolbar>

        <InfiniteScroll
          data="items"
          items-element="#infinite-items"
          :buffer="200"
        >
          <VideoList
            id="infinite-items"
            :items="items?.data"
          />
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
