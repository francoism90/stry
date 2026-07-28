<script setup lang="ts">
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import FilterBar from '@/components/Ui/FilterBar.vue'
import type { LayoutData } from '@/types'

type Props = {
  layout?: LayoutData
}

withDefaults(defineProps<Props>(), {
  layout: () => ({
    title: 'Resources',
    description: 'Browse and manage your resources.',
    id: 'resources.index',
    type: 'video',
  }),
})
</script>

<template>
  <UTheme
    :ui="{
      dropdownMenu: {
        content: 'z-50 min-w-32',
      },
      dashboardPanel: {
        body: 'mx-auto w-full max-w-(--ui-container) flex-1 self-center overflow-visible xl:px-0',
      },
      dashboardNavbar: {
        center: 'mx-auto flex w-full max-w-(--ui-container) justify-between',
        left: 'hidden',
        right: 'hidden',
      },
      dashboardToolbar: {
        left: 'hidden',
        right: 'hidden',
      },
    }"
  >
    <UDashboardPanel id="foo">
      <template #header>
        <AppHeader />
        <FilterBar />
      </template>

      <template #body>
        {{ layout }}
        <slot />
      </template>

      <template #footer>
        <AppFooter />
      </template>
    </UDashboardPanel>
  </UTheme>
</template>
