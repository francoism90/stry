<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
</script>

<template>
  <Head title="Profile" />

  <UPage>
    <UPageBody class="mt-4 space-y-4 px-4 sm:px-6">
      <UPageFeature
        title="Profile"
        description="Manage your account profile and settings."
        class="mb-6"
      />

      <UButton
        label="Logout"
        class="my-2"
        variant="soft"
        @click="() => router.post('/logout')"
      />
    </UPageBody>
  </UPage>
</template>
