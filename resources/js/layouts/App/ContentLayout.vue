<script setup lang="ts">
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import ScopeGroup from '@/components/Ui/ScopeGroup.vue'
import SortMenu from '@/components/Ui/SortMenu.vue'
import type { QueryFilter, QueryValue } from '@/types'
import type { RadioGroupItem, SelectItem, SelectMenuItem } from '@nuxt/ui'

withDefaults(
  defineProps<{
    id?: string
    filters?: SelectMenuItem[]
    scopes?: RadioGroupItem[]
    sorters?: SelectItem[]
    filter?: QueryFilter
    scope?: QueryValue
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
        <AppHeader />

        <UDashboardToolbar>
          <template #left>
            <ScopeGroup
              :items="scopes"
              :active="scope"
            />
          </template>

          <template #right>
            <SortMenu
              :items="sorters"
              :active="sort"
            />
          </template>
        </UDashboardToolbar>
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
