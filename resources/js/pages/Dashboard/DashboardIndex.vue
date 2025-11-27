<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { PageView, VideoCollection } from '@/types'
import { Head } from '@inertiajs/vue3'

interface Props {
  items: VideoCollection
  view?: PageView
}

defineOptions({ layout: DashboardLayout })

defineProps<Props>()
</script>

<template>
  <Head title="Library" />

  <UPage> Dashboard Index </UPage>
</template>
