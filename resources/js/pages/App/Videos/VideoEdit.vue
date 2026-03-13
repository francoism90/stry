<script setup lang="ts">
import VideoEditForm from '@/components/Videos/VideoEditForm.vue'
import VideoLayout from '@/layouts/App/VideoLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { Video } from '@/types'
import { Head } from '@inertiajs/vue3'

defineProps<{
  video: Video
}>()

defineOptions({ layout: [DefaultLayout, VideoLayout] })
</script>

<template>
  <Head :title="video.title" />

  <UPageBody>
    <VideoEditForm :video="video" />
  </UPageBody>
</template>
