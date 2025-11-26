<script setup lang="ts">
import DashboardNavbar from '@/components/Dashboard/DashboardNavbar.vue'
import DashboardSidebar from '@/components/Dashboard/DashboardSidebar.vue'
import ServiceWorker from '@/components/Ui/ServiceWorker.vue'
import { Head } from '@inertiajs/vue3'
</script>

<template>
  <Head>
    <title>Stream videos</title>
  </Head>

  <UApp>
    <UDashboardGroup
      unit="rem"
      class="relative overflow-auto overflow-x-hidden"
    >
      <DashboardSidebar />

      <UDashboardSearch />

      <UDashboardPanel
        id="video"
        :ui="{ body: 'pt-(--ui-header-height) sm:pt-(--ui-header-height)' }"
      >
        <template #header>
          <ServiceWorker />
          <DashboardNavbar />
        </template>

        <template #body>
          <slot />
        </template>
      </UDashboardPanel>
    </UDashboardGroup>
  </UApp>
</template>
