<script setup lang="ts">
import GroupList from '@/components/Groups/GroupList.vue'
import SearchBar from '@/components/Search/SearchBar.vue'
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import FilterToolbar from '@/components/Ui/FilterToolbar.vue'
import type { GroupCollection, OptionItem, QueryFilter, QueryValue } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps<{
  search: string
  items: GroupCollection
  scopes?: OptionItem[]
  sorters?: OptionItem[]
  filter?: QueryFilter
  sort?: QueryValue
  query?: QueryValue
}>()

const itemBody = ref()
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

      <FilterToolbar
        :scopes="scopes"
        :sorters="sorters"
        :filter="filter"
        :sort="sort"
        :query="query"
      />
    </template>

    <template #body>
      <UPage>
        <InfiniteScroll
          data="items"
          :items-element="() => itemBody?.$el"
        >
          <GroupList
            ref="itemBody"
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
