<script setup lang="ts">
import AppHeader from '@/components/Ui/AppHeader.vue'
import ServiceWorker from '@/components/Ui/ServiceWorker.vue'
import { Head } from '@inertiajs/vue3'
</script>

<template>
  <Head>
    <title>Stream videos</title>
  </Head>

  <Suspense>
    <UApp>
      <ServiceWorker />

      <div class="mx-auto w-full max-w-(--ui-container)">
        <AppHeader />
        <slot />
      </div>
    </UApp>
  </Suspense>
</template>
