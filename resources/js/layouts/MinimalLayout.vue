<script setup lang="ts">
import ServiceWorker from '@/components/Ui/ServiceWorker.vue'
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
    <ServiceWorker />

    <UContainer class="flex h-dvh max-h-dvh max-w-md items-center justify-center">
      <slot />
    </UContainer>
  </UApp>
</template>
