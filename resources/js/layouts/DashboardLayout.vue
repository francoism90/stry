<script setup lang="ts"></script>

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
      </template>

      <template #body>
        <slot />
      </template>
    </UDashboardPanel>
  </UDashboardGroup>
</template>
