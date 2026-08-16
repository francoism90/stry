<script setup lang="ts">
import { useAppearance } from '@/composables/appearance'
import { useFlash } from '@/composables/flash'

withDefaults(
  defineProps<{
    unit?: 'rem' | 'px'
    storage?: 'local' | 'cookie'
    storageKey?: string
  }>(),
  {
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
        dashboardToolbar: {
          root: 'mx-auto flex w-full max-w-(--ui-container) flex-col gap-2 border-0 py-4 md:flex-row',
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
        <slot />
      </UDashboardGroup>
    </UTheme>
  </UApp>
</template>
