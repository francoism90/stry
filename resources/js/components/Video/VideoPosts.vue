<script setup lang="ts">
import VideoPost from '@/components/Video/VideoPost.vue'
import type { VideoCollection } from '@/types'

interface Props {
  items?: VideoCollection
  orientation?: 'horizontal' | 'vertical'
}

defineProps<Props>()
</script>

<template>
  <UBlogPosts
    id="page-grid"
    :orientation="orientation || 'vertical'"
  >
    <VideoPost
      v-for="(item, index) in items?.data"
      :key="index"
      :item="item"
    />
  </UBlogPosts>
</template>
