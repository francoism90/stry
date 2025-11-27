<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import VideoLayout from '@/layouts/VideoLayout.vue'
import type { MediaCollection } from '@/types'
import { Head } from '@inertiajs/vue3'

interface Props {
  items: MediaCollection
}

defineOptions({ layout: [DashboardLayout, VideoLayout] })

defineProps<Props>()
</script>

<template>
  <Head title="Library" />

  <UPage>
    {{ items }}
  </UPage>
</template>
