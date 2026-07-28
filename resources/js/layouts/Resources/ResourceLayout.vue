<script setup lang="ts">
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import type { Model } from '@/types'

type Props = {
  title?: string | null
  resource?: Model | undefined
}

withDefaults(defineProps<Props>(), {
  id: '',
  title: '',
  resource: () => <Model>{},
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
    <UDashboardPanel :id="id">
      <template #header>
        <AppHeader />
      </template>

      <template #body>
        {{ resource }}
        <slot />
      </template>

      <template #footer>
        <AppFooter />
      </template>
    </UDashboardPanel>
  </UTheme>
</template>
