<script setup lang="ts">
import AppLogo from '@/components/Ui/AppLogo.vue'
</script>

<template>
  <UDashboardNavbar
    :ui="{ root: 'gap-3 border-0', left: 'w-full' }"
    :toggle="{ variant: 'link', class: 'ps-0' }"
  >
    <template #leading>
      <AppLogo class="lg:hidden" />
    </template>

    <template #right>
      <div class="flex items-center gap-3">
        <UButton
          variant="link"
          color="neutral"
          class="px-0"
          icon="i-lucide-bell"
        />

        <UButton
          variant="link"
          color="neutral"
          class="px-0"
          icon="i-lucide-user"
          to="/admin"
        />
      </div>
    </template>
  </UDashboardNavbar>
</template>
