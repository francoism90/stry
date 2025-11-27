<script setup lang="ts"></script>

<template>
  <UDashboardPanel
    id="videos"
    :ui="{ body: 'pt-(--ui-header-height) sm:pt-(--ui-header-height) lg:ml-72' }"
  >
    <template #header>
      <UDashboardNavbar title="Customers">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>

        <template #right>
          <CustomersAddModal />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <slot />
    </template>
  </UDashboardPanel>
</template>
