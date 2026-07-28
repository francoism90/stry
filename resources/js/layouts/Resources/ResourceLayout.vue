<script setup lang="ts">
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import type { LayoutData } from '@/types'

type Props = {
  layout?: LayoutData
}

withDefaults(defineProps<Props>(), {
  layout: () => ({
    title: 'Resources',
    description: 'Browse and manage your resources.',
    type: 'video',
  }),
})
</script>

<template>
  <UDashboardPanel :id="layout.id ?? 'resources.index'">
    <template #header>
      <AppHeader />
    </template>

    <template #body>
      <slot />
    </template>

    <template #footer>
      <AppFooter />
    </template>
  </UDashboardPanel>
</template>
