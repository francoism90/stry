<script setup lang="ts">
import DashboardFooter from '@/components/Dashboard/DashboardFooter.vue'
</script>

<template>
  <UDashboardGroup>
    <UDashboardPanel id="dashboard">
      <template #header>
        <UDashboardNavbar
          :toggle="false"
          title="Dashboard"
        >
          <template #right>
            <UDashboardSearchButton />
          </template>
        </UDashboardNavbar>

        <UDashboardToolbar />
      </template>

      <template #body>
        <slot />
      </template>

      <template #footer>
        <DashboardFooter />
      </template>
    </UDashboardPanel>
  </UDashboardGroup>
</template>
