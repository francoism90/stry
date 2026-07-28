<script setup lang="ts">
import AppLogo from '@/components/Ui/AppLogo.vue'
import ContentMenu from '@/components/Ui/ContentMenu.vue'
import CreateMenu from '@/components/Ui/CreateMenu.vue'
import UserMenu from '@/components/Ui/UserMenu.vue'
import UserNotifications from '@/components/Ui/UserNotifications.vue'
</script>

<template>
  <UDashboardNavbar :toggle="false">
    <div class="flex items-center gap-2">
      <AppLogo />
      <ContentMenu />
    </div>

    <div class="flex items-center gap-2">
      <CreateMenu />
      <UserNotifications />
      <UserMenu />
    </div>
  </UDashboardNavbar>
</template>
