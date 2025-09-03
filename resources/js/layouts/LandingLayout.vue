<script setup lang="ts">
import ServiceWorker from '@/components/Ui/ServiceWorker.vue'
</script>

<template>
  <UApp>
    <ServiceWorker />

    <slot />
  </UApp>
</template>
