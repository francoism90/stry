<script setup lang="ts">
import SearchBar from '@/components/Search/SearchBar.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import VideoFilters from '@/components/Videos/VideoFilters.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { Filters, VideoCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

defineOptions({ layout: DefaultLayout })

defineProps<{
  search: string
  items: VideoCollection
  sorters: SelectMenuItem[]
  sort: string
  filters: Filters
}>()
</script>

<template>
  <Head :title="search ? `Videos: ${search}` : 'Videos'" />

  <UDashboardPanel id="search-videos">
    <template #header>
      <AppHeader />

      <SearchBar
        :search="search"
        placeholder="Search videos..."
        suffix="/videos"
        :back-href="`/search/${encodeURIComponent(search)}`"
      />
    </template>

    <template #body>
      <UPage>
        <UDashboardToolbar>
          <template #left>
            <VideoFilters
              :results="Boolean(items?.data?.length)"
              :sorters="sorters"
              :sort="sort"
              :captioned="filters?.captioned"
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
