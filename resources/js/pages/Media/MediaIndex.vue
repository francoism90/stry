<script setup lang="ts">
import MediaItems from '@/components/Media/MediaItems.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import VideoLayout from '@/layouts/VideoLayout.vue'
import type { MediaCollection } from '@/types'
import { Deferred, Head } from '@inertiajs/vue3'

defineProps<{
  items: MediaCollection
}>()

defineOptions({ layout: [DashboardLayout, VideoLayout] })
</script>

<template>
  <Head title="Library" />

  <Deferred data="items">
    <template #fallback>
      <div>Loading...</div>
    </template>

    <MediaItems :items="items" />
  </Deferred>
</template>
