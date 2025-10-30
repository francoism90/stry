<script setup lang="ts">
import VideoPost from '@/components/Video/VideoPost.vue'
import type { VideoCollection } from '@/types'

interface Props {
  items?: VideoCollection
}

defineProps<Props>()
</script>

<template>
  <UBlogPosts
    id="page-grid"
    orientation="vertical"
  >
    <VideoPost
      v-for="(item, index) in items?.data"
      :key="index"
      :item="item"
    />
  </UBlogPosts>
</template>
