<script setup lang="ts">
import { useAppearance } from '@/composables/appearance'
import { useFlash } from '@/composables/flash'

const { nonce } = useAppearance()
useFlash()
</script>

<template>
  <UApp :nonce="nonce">
    <UDashboardGroup
      unit="rem"
      storage="local"
      storage-key="app"
      class="relative w-full overflow-clip"
    >
      <slot />
    </UDashboardGroup>
  </UApp>
</template>
