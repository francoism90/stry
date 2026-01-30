<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import AppLogo from './AppLogo.vue'

const back = () => (window && window.history?.length > 1 ? window.history.back() : router.visit('/'))
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
      <UButton
        variant="link"
        size="sm"
        color="neutral"
        label="Back"
        class="px-0"
        icon="i-lucide-circle-arrow-left"
        @click="back"
      />
    </template>
  </UDashboardNavbar>
</template>
