<script setup lang="ts">
import AppLogo from '@/components/Ui/AppLogo.vue'
import { useAppearance } from '@/composables/appearance'
import { useFlash } from '@/composables/flash'

const { nonce } = useAppearance()
useFlash()
</script>

<template>
  <UApp :nonce="nonce">
    <UContainer class="mx-auto flex min-h-dvh w-full max-w-(--ui-container) flex-col gap-6 py-4 sm:py-6">
      <AppLogo />

      <slot />
    </UContainer>
  </UApp>
</template>
