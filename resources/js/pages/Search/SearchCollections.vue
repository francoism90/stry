<script setup lang="ts">
import GroupFilterBar from '@/components/Groups/GroupFilterBar.vue'
import GroupList from '@/components/Groups/GroupList.vue'
import SearchBar from '@/components/Search/SearchBar.vue'
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import type { GroupCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

defineProps<{
  search: string
  items: GroupCollection
  sorters: SelectMenuItem[]
  sort: string
}>()
</script>

<template>
  <Head :title="search ? `Collections: ${search}` : 'Collections'" />

  <UDashboardPanel id="search-collections">
    <template #header>
      <AppHeader />

      <SearchBar
        :search="search"
        placeholder="Search collections..."
        suffix="/collections"
        :back-href="`/search/${encodeURIComponent(search)}`"
      />
    </template>

    <template #body>
      <UPage>
        <UDashboardToolbar>
          <template #left>
            <GroupFilterBar
              :results="Boolean(items?.data?.length)"
              :sorters="sorters"
              :sort="sort"
            />
          </template>
        </UDashboardToolbar>

        <InfiniteScroll
          data="items"
          items-element="#infinite-items"
          :buffer="200"
        >
          <GroupList
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
