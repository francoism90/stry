<script setup lang="ts">
import AppLogo from '@/components/Ui/AppLogo.vue'
import { useAppearance } from '@/composables/appearance'
import { useFlash } from '@/composables/flash'
import { Head } from '@inertiajs/vue3'

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

  <Suspense>
    <UApp :nonce="nonce">
      <UContainer class="flex h-dvh max-h-dvh flex-col items-center justify-center gap-3 py-4 sm:py-6">
        <div class="py-2">
          <AppLogo />
        </div>

        <slot />
      </UContainer>
    </UApp>
  </Suspense>
</template>
