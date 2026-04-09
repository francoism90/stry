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
        <UDashboardToolbar>
          <template #left>
            <TagFilters
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
          <div id="infinite-items">
            <UEmpty
              v-if="!items?.data?.length"
              icon="i-lucide-tags"
              title="No tags"
            />

            <TagList
              v-else
              :items="items?.data"
            />
          </div>
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
