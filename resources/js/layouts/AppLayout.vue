<script setup lang="ts">
import AppSidebar from '@/components/Ui/AppSidebar.vue'
import { useAppearance } from '@/composables/appearance'
import { useFlash } from '@/composables/flash'

withDefaults(
  defineProps<{
    mode?: 'drawer' | 'slideover' | 'modal'
    unit?: 'rem' | 'px'
    storage?: 'local' | 'cookie'
    storageKey?: string
  }>(),
  {
    mode: 'slideover',
    unit: 'rem',
    storage: 'local',
    storageKey: 'app',
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
          body: 'mx-auto flex w-full max-w-(--ui-container) flex-1 overflow-visible py-0 sm:py-0',
        },
        dashboardNavbar: {
          center: 'flex',
        },
        dashboardToolbar: {
          root: 'mx-auto flex w-full max-w-(--ui-container) flex-col items-stretch gap-3 border-0 py-4 md:flex-row md:items-center',
        },
        dashboardSidebarToggle: {
          base: 'lg:inline-flex',
        },
        header: {
          root: 'relative',
        },
        dropdownMenu: {
          content: 'z-50 min-w-32',
        },
      }"
    >
      <UDashboardGroup
        :unit="unit"
        :storage="storage"
        :storage-key="storageKey"
        class="relative overflow-clip"
      >
        <AppSidebar :mode="mode" />

        <slot />
      </UDashboardGroup>
    </UTheme>
  </UApp>
</template>
