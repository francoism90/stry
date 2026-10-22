<script setup lang="ts">
import AppFooter from '@/components/Ui/AppFooter.vue'
import FilterToolbar from '@/components/Ui/FilterToolbar.vue'
import StickyHeader from '@/components/Ui/StickyHeader.vue'
import { provideQuery } from '@/composables/query'
import type { OptionItem, QueryFilter, QueryValue } from '@/types'
import type { ButtonProps } from '@nuxt/ui'

const props = withDefaults(
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
    sort: null,
    query: null,
  },
)

provideQuery(props)
</script>

<template>
  <UDashboardPanel :id="id">
    <template #header>
      <StickyHeader :collapsible="!!title">
        <UPageHeader
          :title="title"
          :description="description"
          :links="links"
          class="mx-auto w-full max-w-(--ui-container) px-4 py-4 sm:px-6"
        />

        <template #toolbar>
          <FilterToolbar
            :scopes="scopes"
            :sorters="sorters"
          />
        </template>
      </StickyHeader>
    </template>

    <template #body>
      <slot />
    </template>

    <template #footer>
      <AppFooter />
    </template>
  </UDashboardPanel>
</template>
