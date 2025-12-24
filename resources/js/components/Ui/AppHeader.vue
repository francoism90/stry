<script setup lang="ts">
import AppSidebar from '@/components/Ui/AppSidebar.vue'
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

      <UDashboardGroup
        unit="rem"
        storage="local"
        class="relative overflow-clip"
      >
        <AppSidebar />

        <UDashboardSearch />

        <slot />
      </UDashboardGroup>
    </UApp>
  </Suspense>
</template>
