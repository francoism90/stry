<script setup lang="ts">
import AppLogo from '@/components/Ui/AppLogo.vue'
import ContentMenu from '@/components/Ui/ContentMenu.vue'
import CreateMenu from '@/components/Ui/CreateMenu.vue'
import UserMenu from '@/components/Ui/UserMenu.vue'
import UserNotifications from '@/components/Ui/UserNotifications.vue'
</script>

<template>
  <div class="w-full">
    <UHeader :toggle="false">
      <template #left>
        <AppLogo />
        <ContentMenu />
      </template>

      <template #right>
        <div class="flex items-center gap-2">
          <CreateMenu />
          <UserNotifications />
          <UserMenu />
        </div>
      </template>
    </UHeader>
  </div>
</template>
