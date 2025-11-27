<script setup lang="ts">
import DashboardSidebar from '@/components/Dashboard/DashboardSidebar.vue'
import { Head } from '@inertiajs/vue3'
</script>

<template>
  <Head>
    <title>Stream videos</title>
  </Head>

  <Suspense>
    <UApp>
      <UDashboardGroup
        unit="rem"
        storage="local"
        class="relative overflow-clip"
      >
        <DashboardSidebar />

        <UDashboardSearch />

        <slot />
      </UDashboardGroup>
    </UApp>
  </Suspense>
</template>
