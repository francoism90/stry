<script setup lang="ts">
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import FilterBar from '@/components/Ui/FilterBar.vue'
import { useFlash } from '@/composables/flash'
import type { QueryFilter, QueryValue } from '@/types'
import type { SelectMenuItem } from '@nuxt/ui'

withDefaults(
  defineProps<{
    id?: string
    showFilter?: boolean
    filters?: SelectMenuItem[]
    sorters?: SelectMenuItem[]
    filter?: QueryFilter
    query?: QueryValue
    sort?: QueryValue
  }>(),
  {
    id: 'resources.index',
    showFilter: true,
  },
)
useFlash()
</script>

<template>
  <UDashboardPanel :id="id ?? 'resources.index'">
    <template #header>
      <AppHeader />
      <FilterBar
        v-if="showFilter"
        :filters="filters"
        :sorters="sorters"
        :query="query"
        :filter="filter"
        :sort="sort"
      />
    </template>

    <template #body>
      content
      {{ id }}
      {{ filters }}
      <slot />
    </template>

    <template #footer>
      <AppFooter />
    </template>
  </UDashboardPanel>
</template>
