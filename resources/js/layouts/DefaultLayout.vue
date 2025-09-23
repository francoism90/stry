<script lang="ts" setup>
import AppHeader from '@/components/Ui/AppHeader.vue'
import ServiceWorker from '@/components/Ui/ServiceWorker.vue'
</script>

<template>
  <UApp>
    <ServiceWorker />

    <UMain>
      <AppHeader />

      <UContainer class="contain-inline-size contain-layout contain-paint contain-style">
        <slot />
      </UContainer>
    </UMain>
  </UApp>
</template>
