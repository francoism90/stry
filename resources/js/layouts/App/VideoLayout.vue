<script setup lang="ts">
import AppHeader from '@/components/Ui/AppHeader.vue'
import type { Video } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'

const props = defineProps<{
  video: Video
}>()

useEcho<Video>(`videos.${props.video.id}`, '.video.updated', () => router.reload({ only: ['video'] }))
useEcho<Video>(`videos.${props.video.id}`, '.playlist.created', () => router.reload({ only: ['playlist'] }))
useEcho<Video>(`videos.${props.video.id}`, '.playlist.updated', () => router.reload({ only: ['playlist'] }))
</script>

<template>
  <Head :title="video.title" />

  <UDashboardPanel id="video-resource">
    <template #header>
      <AppHeader />
    </template>

    <template #body>
      <slot />
    </template>
  </UDashboardPanel>
</template>
