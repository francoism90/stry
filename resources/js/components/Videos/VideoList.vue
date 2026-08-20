<script setup lang="ts">
import VideoCard from '@/components/Videos/VideoCard.vue'
import type { VideoCollection } from '@/types'

defineProps<{
  items: VideoCollection | undefined
}>()
</script>

<template>
  <UBlogPosts class="grid grid-cols-1 gap-4 gap-y-6 sm:grid-cols-3 lg:gap-y-8 xl:grid-cols-4">
    <VideoCard
      v-for="(item, index) in items?.data ?? []"
      :key="item.id"
      :item="item"
      :index="index"
    />
  </UBlogPosts>
</template>
