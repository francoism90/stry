<script setup lang="ts">
import VideoList from '@/components/Videos/VideoList.vue'
import ContentLayout from '@/layouts/App/ContentLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { VideoCollection } from '@/types'
import { Head } from '@inertiajs/vue3'

defineProps<{
  items: VideoCollection
}>()

defineOptions({
  layout: [
    [AppLayout, { title: 'Videos' }],
    [ContentLayout, { id: 'videos.index', title: 'Videos' }],
  ],
})
</script>

<template>
  <Head title="Videos" />

  <UPage>
    <VideoList
      id="video-index"
      :items="items?.data"
    />
  </UPage>
</template>
