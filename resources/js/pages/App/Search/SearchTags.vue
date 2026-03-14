<script setup lang="ts">
import SearchBar from '@/components/Search/SearchBar.vue'
import TagFilters from '@/components/Tags/TagFilters.vue'
import TagList from '@/components/Tags/TagList.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { TagCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

defineOptions({ layout: DefaultLayout })

defineProps<{
  search: string
  items: TagCollection
  types: SelectMenuItem[]
  type?: string | null
}>()
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
    </template>

    <template #body>
      <UPage>
        <TagFilters
          :types="types"
          :type="type ?? undefined"
        />

        <InfiniteScroll
          data="items"
          :buffer="200"
        >
          <TagList :items="items?.data" />
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
