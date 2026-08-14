<script setup lang="ts">
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'

withDefaults(
  defineProps<{
    id?: string
  }>(),
  {
    id: 'resource',
  },
)
</script>

<template>
  <UDashboardPanel :id="id">
    <template #header>
      <AppHeader />
    </template>

    <template #body>
      <div class="mx-auto flex w-full flex-col gap-4 sm:gap-6 lg:max-w-5xl lg:gap-12">
        <slot />
      </div>
    </template>

    <template #footer>
      <AppFooter />
    </template>
  </UDashboardPanel>
</template>
