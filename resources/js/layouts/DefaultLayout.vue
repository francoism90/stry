<script setup lang="ts">
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
    <UContainer class="flex h-dvh max-h-dvh items-center justify-center">
      <slot />
    </UContainer>
  </UApp>
</template>
