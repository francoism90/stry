<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { Head } from '@inertiajs/vue3'

defineOptions({ layout: DashboardLayout })
</script>

<template>
  <Head title="Library" />

  <UPage> Dashboard Index </UPage>
</template>
