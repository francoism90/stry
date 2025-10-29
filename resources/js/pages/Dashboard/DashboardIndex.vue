<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import { Head } from '@inertiajs/vue3'

defineOptions({ layout: [DefaultLayout, DashboardLayout] })
</script>

<template>
  <Head title="Library" />

  <UPage>
    <UPageBody>
      <UContainer>
        <UPageHeader title="Library" />
      </UContainer>
    </UPageBody>
  </UPage>
</template>
