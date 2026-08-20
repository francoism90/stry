<script setup lang="ts">
import SearchBar from '@/components/Search/SearchBar.vue'
import TagFilterBar from '@/components/Tags/TagFilterBar.vue'
import TagList from '@/components/Tags/TagList.vue'
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import type { TagCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

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
        <UDashboardToolbar>
          <template #left>
            <TagFilterBar
              :results="Boolean(items?.data?.length)"
              :types="types"
              :type="type ?? undefined"
            />
          </template>
        </UDashboardToolbar>

        <InfiniteScroll
          data="items"
          items-element="#infinite-items"
          :buffer="200"
        >
          <TagList
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
