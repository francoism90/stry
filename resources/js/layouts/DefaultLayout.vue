<script setup lang="ts">
import AppHeader from '@/components/Ui/AppHeader.vue'
import { Head } from '@inertiajs/vue3'
</script>

<template>
  <Head>
    <title>Stream videos</title>
    <meta
      head-key="description"
      name="description"
      content="Stream and watch your favorite videos."
    />
  </Head>

  <UApp>
    <UContainer class="border-default pt-10 sm:border-x">
      <AppHeader />
      <slot />
      <AppFooter />
    </UContainer>
  </UApp>
</template>
