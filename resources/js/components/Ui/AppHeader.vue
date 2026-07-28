<script setup lang="ts">
import AppLogo from '@/components/Ui/AppLogo.vue'
import ContentMenu from '@/components/Ui/ContentMenu.vue'
import CreateMenu from '@/components/Ui/CreateMenu.vue'
import UserMenu from '@/components/Ui/UserMenu.vue'
import UserNotifications from '@/components/Ui/UserNotifications.vue'
</script>

<template>
  <header class="sticky top-0 z-40 w-full bg-neutral-900/80 backdrop-blur-md backdrop-saturate-150">
    <UDashboardNavbar :toggle="false">
      <template #left>
        <AppLogo />
        <ContentMenu />
      </template>

      <template #right>
        <CreateMenu />
        <UserNotifications />
        <UserMenu />
      </template>
    </UDashboardNavbar>

    <slot />
  </header>
</template>
