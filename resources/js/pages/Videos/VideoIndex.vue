<script setup lang="ts">
import ContentLayout from '@/layouts/App/ContentLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { VideoCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'

defineProps<{
  items: VideoCollection
}>()

defineOptions({
  layout: [
    [AppLayout, { title: 'Videos' }],
    [ContentLayout, { id: 'videos.index' }],
  ],
})
</script>

<template>
  <Head title="Videos" />

  <UPage>
    <InfiniteScroll
      data="items"
      items-element="#video-index"
      :buffer="200"
    >
      {{ items.data }}
    </InfiniteScroll>
  </UPage>
</template>
