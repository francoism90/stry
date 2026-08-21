<script setup lang="ts">
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import FilterToolbar from '@/components/Ui/FilterToolbar.vue'
import SearchTypeSwitcher from '@/components/Ui/SearchTypeSwitcher.vue'
import type { OptionItem, QueryFilter, QueryValue } from '@/types'
import type { ButtonProps } from '@nuxt/ui'

withDefaults(
  defineProps<{
    id?: string
    title?: string
    description?: string
    links?: ButtonProps[]
    scopes?: OptionItem[]
    sorters?: OptionItem[]
    filter?: QueryFilter
    sort?: QueryValue
    query?: QueryValue
  }>(),
  {
    id: 'resources.index',
  },
)
</script>

<template>
  <UDashboardPanel :id="id">
    <template #header>
      <div class="sticky top-0 z-50 bg-default/75 backdrop-blur">
        <AppHeader :title="title" />

        <SearchTypeSwitcher
          :id="id"
          :query="query"
        />

        <FilterToolbar
          :scopes="scopes"
          :sorters="sorters"
          :filter="filter"
          :sort="sort"
          :query="query"
        />
      </div>
    </template>

    <template #body>
      <slot />
    </template>

    <template #footer>
      <AppFooter />
    </template>
  </UDashboardPanel>
</template>
