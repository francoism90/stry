<script setup lang="ts">
import { useAppearance } from '@/composables/appearance'
import { useFlash } from '@/composables/flash'
import type { LayoutData } from '@/types'
import { Head } from '@inertiajs/vue3'

defineProps<{
  layout: LayoutData
}>()

const { nonce } = useAppearance()
useFlash()
</script>

<template>
  <Head>
    <title>Account</title>
    <meta
      head-key="description"
      name="description"
      content="Manage your account and access your dashboard."
    />
  </Head>

  <UApp :nonce="nonce">
<<<<<<< HEAD
    <UDashboardGroup
      unit="rem"
      storage="local"
      storage-key="auth"
      class="relative overflow-clip"
    >
      <slot />
    </UDashboardGroup>
=======
    <UContainer class="flex h-dvh max-h-dvh flex-col items-center justify-center gap-3 py-4 sm:py-6">
      <div class="py-2">
        <AppLogo />
      </div>

      <slot />
    </UContainer>
>>>>>>> 8853b7db (fix: remove unnecessary Suspense wrapper in AuthLayout component)
  </UApp>
</template>
