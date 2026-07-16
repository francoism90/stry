<script setup lang="ts">
import SearchBar from '@/components/Search/SearchBar.vue'
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import VideoFilterBar from '@/components/Videos/VideoFilterBar.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { VideoCollection, VideoFilters } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

defineOptions({ layout: DefaultLayout })

defineProps<{
  search: string
  items: VideoCollection
  filters: VideoFilters
  sorters: SelectMenuItem[]
  scopes: SelectMenuItem[]
  sort: string
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

    <template #footer>
      <AppFooter />
    </template>
  </UDashboardPanel>
</template>
