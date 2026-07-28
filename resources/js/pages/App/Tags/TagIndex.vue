<script setup lang="ts">
import TagCreateModal from '@/components/Tags/TagCreateModal.vue'
import TagList from '@/components/Tags/TagList.vue'
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import FilterBar from '@/components/Ui/FilterBar.vue'
import { useAuth } from '@/composables/auth'
import type { QueryFilter, QueryFilters, TagCollection } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

defineProps<{
  items: TagCollection
  filters?: SelectMenuItem[]
  sorters?: SelectMenuItem[]
  filter?: QueryFilters
  sort?: QueryFilter
}>()

const { hasAnyRole } = useAuth()
</script>

<template>
  <UDashboardPanel id="tags">
    <template #header>
      <AppHeader />
    </template>

    <template #body>
      <UPage>
        <UDashboardToolbar>
          <template #left>
            <FilterBar
              :results="Boolean(items?.data?.length)"
              :filters="filters"
              :sorters="sorters"
              :filter="filter"
              :sort="sort"
            />
          </template>

          <template
            v-if="hasAnyRole(['admin', 'super-admin'])"
            #right
          >
            <TagCreateModal :types="filters" />
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
