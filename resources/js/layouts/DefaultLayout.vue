<script lang="ts" setup>
import AppHeader from '@/components/Ui/AppHeader.vue'
import ServiceWorker from '@/components/Ui/ServiceWorker.vue'
</script>

<template>
  <UApp>
    <ServiceWorker />

    <UMain class="contain-inline-size contain-layout contain-paint contain-style">
      <AppHeader />
      <slot />
    </UMain>
  </UApp>
</template>
