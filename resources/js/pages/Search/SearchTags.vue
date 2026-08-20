<script setup lang="ts">
import SearchBar from '@/components/Search/SearchBar.vue'
import TagList from '@/components/Tags/TagList.vue'
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import FilterToolbar from '@/components/Ui/FilterToolbar.vue'
import type { OptionItem, QueryFilter, QueryValue, TagCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps<{
  search: string
  items: TagCollection
  scopes?: OptionItem[]
  sorters?: OptionItem[]
  filter?: QueryFilter
  sort?: QueryValue
  query?: QueryValue
}>()

const itemBody = ref()
</script>

<template>
  <Head :title="search ? `Tags: ${search}` : 'Tags'" />

  <UDashboardPanel id="search-tags">
    <template #header>
      <AppHeader />

      <SearchBar
        :search="search"
        placeholder="Search tags..."
        suffix="/tags"
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
          <TagList
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
