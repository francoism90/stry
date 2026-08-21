<script setup lang="ts">
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import FilterToolbar from '@/components/Ui/FilterToolbar.vue'
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
  },
)

provideQuery(props)
</script>

<template>
  <UDashboardPanel :id="id">
    <template #header>
      <div class="sticky top-0 z-50 bg-default/75 backdrop-blur">
        <AppHeader />

        <UPageHeader
          v-if="title"
          :title="title"
          :description="description"
          :links="links"
          class="mx-auto w-full max-w-(--ui-container) px-4 py-4 sm:px-6"
        />

        <FilterToolbar
          :scopes="scopes"
          :sorters="sorters"
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
