<script setup lang="ts">
import SearchBar from '@/components/Search/SearchBar.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import VideoFilters from '@/components/Videos/VideoFilters.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { VideoCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

defineOptions({ layout: DefaultLayout })

defineProps<{
  search: string
  items: VideoCollection
  orders: SelectMenuItem[]
  order: string
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
              :orders="orders"
              :order="order"
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
