<script setup lang="ts">
import VideoCard from '@/components/Video/VideoCard.vue'
import type { VideoCollection } from '@/types'

interface Props {
  items: VideoCollection | null | undefined
  orientation?: 'horizontal' | 'vertical' | undefined
}

defineProps<Props>()
</script>

<template>
  <UBlogPosts :orientation="orientation">
    <VideoCard
      v-for="item in items?.data"
      :key="item.id"
      :item="item"
    />
  </UBlogPosts>
</template>
