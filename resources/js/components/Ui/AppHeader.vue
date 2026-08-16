<script setup lang="ts">
import AppLogo from '@/components/Ui/AppLogo.vue'
import CreateMenu from '@/components/Ui/CreateMenu.vue'
import GlobalSearch from '@/components/Ui/GlobalSearch.vue'
import UserMenu from '@/components/Ui/UserMenu.vue'
import UserNotifications from '@/components/Ui/UserNotifications.vue'
</script>

<template>
  <UDashboardNavbar>
    <template #leading>
      <UDashboardSidebarToggle />
    </template>

    <template #left>
      <AppLogo class="ms-2 hidden lg:flex" />
    </template>

    <GlobalSearch />

    <template #right>
      <div class="flex items-center gap-2">
        <CreateMenu />
        <UserNotifications />
        <UserMenu />
      </div>
    </template>
  </UDashboardNavbar>
</template>
