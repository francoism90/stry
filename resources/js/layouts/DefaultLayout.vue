<script setup lang="ts">
import AppLogo from '@/components/Ui/AppLogo.vue'
import { Head } from '@inertiajs/vue3'
</script>

<template>
  <Head>
    <title>Stream videos</title>
    <meta
      head-key="description"
      name="description"
      content="Stream and watch your favorite videos."
    />
  </Head>

  <UApp>
    <UContainer class="flex h-dvh max-h-dvh flex-col items-center justify-center gap-3 py-4 sm:py-6">
      <AppLogo />
      <slot />
    </UContainer>
  </UApp>
</template>
