<script setup lang="ts">
import VideoCard from '@/components/Videos/VideoCard.vue'
import type { Video } from '@/types'

defineProps<{
  items: Video[] | undefined
}>()
</script>

<template>
<<<<<<< HEAD
  <UBlogPosts class="gap-6 lg:gap-y-8 xl:grid-cols-4">
=======
  <UBlogPosts class="grid grid-cols-1 gap-4 gap-y-6 sm:grid-cols-3 xl:grid-cols-4">
>>>>>>> caaed9fe (fix: refactor tag and video list components to use dynamic color and update layout structure)
    <VideoCard
      v-for="(item, index) in items"
      :key="item.id"
      :item="item"
      :index="index"
    />
  </UBlogPosts>
</template>
