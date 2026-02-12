<script setup lang="ts">
import AppLogo from '@/components/Ui/AppLogo.vue'
import ServiceWorker from '@/components/Ui/ServiceWorker.vue'
import { useAppearance } from '@/composables/appearance'
import { Head } from '@inertiajs/vue3'

const { nonce } = useAppearance()
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
      <ServiceWorker />

      <UContainer class="flex h-dvh max-h-dvh flex-col items-center justify-center gap-3 py-4 sm:py-6">
        <AppLogo />
        <slot />
      </UContainer>
    </UApp>
  </Suspense>
</template>
