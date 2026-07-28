<script setup lang="ts">
import VideoCard from '@/components/Videos/VideoCard.vue'
import type { Video } from '@/types'

defineProps<{
  items: Video[] | undefined
}>()
</script>

<template>
  <UBlogPosts class="gap-6 lg:gap-y-8 xl:grid-cols-4">
    <VideoCard
      v-for="(item, index) in items"
      :key="item.id"
      :item="item"
      :index="index"
    />
  </UBlogPosts>
</template>
