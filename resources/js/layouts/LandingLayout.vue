<script setup lang="ts">
import FlashAlert from '@/components/Ui/FlashAlert.vue'
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
    <FlashAlert />

    <slot />
  </UApp>
</template>
