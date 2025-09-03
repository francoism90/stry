<script setup lang="ts">
import ServiceWorker from '@/components/Ui/ServiceWorker.vue'
</script>

<template>
  <UApp>
    <ServiceWorker />

    <UContainer class="flex h-dvh max-h-dvh max-w-md items-center justify-center">
      <slot />
    </UContainer>
  </UApp>
</template>
