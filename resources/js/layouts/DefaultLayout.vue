<script lang="ts" setup>
import AppHeader from '@/components/Ui/AppHeader.vue'
import ServiceWorker from '@/components/Ui/ServiceWorker.vue'
</script>

<template>
  <UApp>
    <ServiceWorker />

    <UMain>
      <AppHeader />

      <div class="contain-inline-size contain-layout contain-paint contain-style">
        <slot />
      </div>
    </UMain>
  </UApp>
</template>
