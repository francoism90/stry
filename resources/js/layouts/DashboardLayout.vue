<script setup lang="ts">
import DashboardNavbar from '@/components/Dashboard/DashboardNavbar.vue'
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
    <UDashboardGroup>
      <UDashboardPanel id="dashboard">
        <template #header>
          <DashboardNavbar />
        </template>

        <template #body>
          <UMain>
            <slot />
          </UMain>
        </template>
      </UDashboardPanel>
    </UDashboardGroup>
  </UApp>
</template>
