<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
</script>

<template>
  <Head title="Profile" />

  <UPageBody>
    <UPageFeature title="Profile" />

    <UButton
      label="Logout"
      class="w-fit"
      variant="soft"
      @click="() => router.post('/logout')"
    />
  </UPageBody>
</template>
