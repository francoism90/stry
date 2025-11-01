<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import { Head, router } from '@inertiajs/vue3'

defineOptions({ layout: [DefaultLayout, DashboardLayout] })
</script>

<template>
  <Head title="Profile" />

  <UPage>
    <UPageBody>
      <UContainer>
        <UPageHeader title="Profile" />

        <UButton
          label="Logout"
          class="my-2"
          variant="soft"
          @click="() => router.post('/logout')"
        />
      </UContainer>
    </UPageBody>
  </UPage>
</template>
