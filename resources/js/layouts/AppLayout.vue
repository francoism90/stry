<script setup lang="ts">
import { useAppearance } from '@/composables/appearance'
import { useFlash } from '@/composables/flash'
import type { LayoutData } from '@/types'

defineProps<{
  layout?: LayoutData
}>()

const { nonce } = useAppearance()
useFlash()
</script>

<template>
  <UApp :nonce="nonce">
    <UDashboardGroup
      unit="rem"
      storage="local"
      storage-key="app"
      class="relative overflow-clip"
    >
      <slot />
    </UDashboardGroup>
  </UApp>
</template>
