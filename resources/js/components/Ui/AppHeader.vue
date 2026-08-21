<script setup lang="ts">
import CreateMenu from '@/components/Ui/CreateMenu.vue'
import GlobalSearch from '@/components/Ui/GlobalSearch.vue'
import UserMenu from '@/components/Ui/UserMenu.vue'
import UserNotifications from '@/components/Ui/UserNotifications.vue'

defineProps<{
  title?: string | undefined
}>()
</script>

<template>
  <UDashboardNavbar :title="title">
    <template #leading>
      <UDashboardSidebarToggle />
    </template>

    <template #left>
      <GlobalSearch />
    </template>

    <template #right>
      <div class="flex items-center gap-2">
        <CreateMenu />
        <UserNotifications />
        <UserMenu />
      </div>
    </template>
  </UDashboardNavbar>
</template>
