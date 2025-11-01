<script setup lang="ts">
import DashboardNavbar from '@/components/Dashboard/DashboardNavbar.vue'
</script>

<template>
  <UDashboardGroup>
    <UDashboardPanel id="dashboard">
      <template #header>
        <DashboardNavbar />
      </template>

      <template #body>
        <slot />
      </template>
    </UDashboardPanel>
  </UDashboardGroup>
</template>
