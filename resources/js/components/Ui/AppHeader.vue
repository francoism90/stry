<script setup lang="ts">
import AppLogo from '@/components/Ui/AppLogo.vue'
import ContentMenu from '@/components/Ui/ContentMenu.vue'
import CreateMenu from '@/components/Ui/CreateMenu.vue'
import UserMenu from '@/components/Ui/UserMenu.vue'
import UserNotifications from '@/components/Ui/UserNotifications.vue'
</script>

<template>
  <div class="sticky top-0 z-50 border-b border-default bg-default/75 backdrop-blur">
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

    <slot />
  </div>
</template>
