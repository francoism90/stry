<script setup lang="ts">
import ContentScopeBar from '@/components/Content/ContentScopeBar.vue'
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import type { QueryFilter, QueryValue } from '@/types'
import type { SelectMenuItem, TabsItem } from '@nuxt/ui'

withDefaults(
  defineProps<{
    id?: string
    title?: string
    scopes?: TabsItem[]
    scope?: QueryValue
    filters?: SelectMenuItem[]
    filter?: QueryFilter
    sorters?: SelectMenuItem[]
    sort?: QueryValue
    query?: QueryValue
  }>(),
  {
    id: 'resources.index',
    title: 'Resources',
  },
)
</script>

<template>
  <UDashboardPanel :id="id">
    <template #header>
      <div class="sticky top-0 z-50 bg-default/75 backdrop-blur">
        <AppHeader />

        <ContentScopeBar
          :title="title"
          :active="scope"
          :items="scopes"
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
