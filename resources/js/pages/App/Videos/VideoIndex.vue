<script setup lang="ts">
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import FilterBar from '@/components/Ui/FilterBar.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import type { QueryFilter, QueryFilters, VideoCollection } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

defineProps<{
  items: VideoCollection
  filters?: SelectMenuItem[]
  sorters?: SelectMenuItem[]
  filter?: QueryFilters
  sort?: QueryFilter
  query?: QueryFilter
}>()
</script>

<template>
  <UDashboardPanel id="videos">
    <template #header>
      <AppHeader>
        <FilterBar
          placeholder="Search videos"
          :results="Boolean(items?.data?.length)"
          :filters="filters"
          :sorters="sorters"
          :filter="filter"
          :sort="sort"
          :query="query"
        />
      </AppHeader>
    </template>

    <template #body>
      <UPage>
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

    <template #footer>
      <AppFooter />
    </template>
  </UDashboardPanel>
</template>
