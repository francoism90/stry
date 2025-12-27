<script setup lang="ts">
import DashboardSidebar from '@/components/Admin/DashboardSidebar.vue'
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
        class="relative w-full overflow-clip"
      >
        <DashboardSidebar />

        <UDashboardSearch />

        <slot />
      </UDashboardGroup>
    </UApp>
  </Suspense>
</template>
