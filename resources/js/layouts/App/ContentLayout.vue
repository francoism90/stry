<script setup lang="ts">
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import FilterBar from '@/components/Ui/FilterBar.vue'
import { useFlash } from '@/composables/flash'

withDefaults(
  defineProps<{
    id?: string
    showFilter?: boolean
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
      <FilterBar v-if="showFilter" />
    </template>

    <template #body>
      content
      {{ id }}
      <slot />
    </template>

    <template #footer>
      <AppFooter />
    </template>
  </UDashboardPanel>
</template>
