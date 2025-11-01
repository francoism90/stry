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
        <div class="contain-inline-size contain-layout contain-paint contain-style">
          <slot />
        </div>
      </template>
    </UDashboardPanel>
  </UDashboardGroup>
</template>
