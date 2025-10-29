<script setup lang="ts">
import DashboardNavigation from '@/components/Dashboard/DashboardNavigation.vue'
</script>

<template>
  <UDashboardGroup>
    <UDashboardPanel id="dashboard">
      <template #header>
        <DashboardNavigation />
      </template>

      <template #body>
        <slot />
      </template>
    </UDashboardPanel>
  </UDashboardGroup>
</template>
