<script setup lang="ts">
import VideoLayout from '@/layouts/App/VideoLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { Video } from '@/types'
import { Head } from '@inertiajs/vue3'

const props = defineProps<{
  video: Video
}>()

defineOptions({ layout: [DefaultLayout, VideoLayout] })
</script>

<template>
  <Head :title="video.title" />

  <UPage> edit </UPage>
</template>
