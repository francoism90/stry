<script setup lang="ts">
import AppFooter from '@/components/Ui/AppFooter.vue'
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

  <Suspense>
    <UApp>
      <UContainer>
        <AppHeader />
        <slot />
        <AppFooter />
      </UContainer>
    </UApp>
  </Suspense>
</template>
