<script setup lang="ts">
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import FilterToolbar from '@/components/Ui/FilterToolbar.vue'
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
    fluid?: boolean
  }>(),
  {
    id: 'resource',
    fluid: false,
  },
)
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
          v-if="scopes || sorters"
          :scopes="scopes"
          :sorters="sorters"
          :filter="filter"
          :sort="sort"
          :query="query"
        />
      </div>
    </template>

    <template #body>
      <div
        v-if="!fluid"
        class="mx-auto flex w-full flex-col gap-4 sm:gap-6 lg:max-w-5xl lg:gap-12"
      >
        <slot />
      </div>

      <slot v-else />
    </template>

    <template #footer>
      <AppFooter />
    </template>
  </UDashboardPanel>
</template>
