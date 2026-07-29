<script setup lang="ts">
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import FilterBar from '@/components/Ui/FilterBar.vue'
import { useAppearance } from '@/composables/appearance'
import { useFlash } from '@/composables/flash'

withDefaults(
  defineProps<{
    id?: string
    showFilter?: boolean
  }>(),
  {
    id: 'resources.index',
    showFilter: false,
  },
)

const { nonce } = useAppearance()
useFlash()
</script>

<template>
  <UApp :nonce="nonce">
    <UTheme
      :ui="{
        dashboardPanel: {
          body: 'mx-auto flex w-full max-w-(--ui-container) flex-1 overflow-visible',
        },
        dashboardNavbar: {
          root: 'mx-auto flex w-full max-w-(--ui-container) border-0',
        },
        dashboardToolbar: {
          root: 'mx-auto flex w-full max-w-(--ui-container) flex-col items-stretch gap-2 border-0 py-3 md:flex-row',
        },
        dropdownMenu: {
          content: 'z-50 min-w-32',
        },
      }"
    >
      <UDashboardGroup
        unit="rem"
        storage="local"
        storage-key="app"
        class="relative overflow-clip"
      >
        <UDashboardPanel :id="id ?? 'resources.index'">
          <template #header>
            <AppHeader />
          </template>

          <template #body>
            <FilterBar v-if="showFilter" />

            {{ id }}

            <slot />
          </template>

          <template #footer>
            <AppFooter />
          </template>
        </UDashboardPanel>
      </UDashboardGroup>
    </UTheme>
  </UApp>
</template>
