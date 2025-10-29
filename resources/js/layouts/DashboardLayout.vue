<script setup lang="ts">
import DashboardFooter from '@/components/Dashboard/DashboardFooter.vue'
</script>

<template>
  <UDashboardGroup>
    <UDashboardPanel id="dashboard">
      <template #body>
        <slot />
      </template>

      <template #footer>
        <DashboardFooter />
      </template>
    </UDashboardPanel>
  </UDashboardGroup>
</template>
