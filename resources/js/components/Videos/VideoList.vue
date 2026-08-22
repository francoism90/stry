<script setup lang="ts">
import VideoCard from '@/components/Videos/VideoCard.vue'
import type { Video } from '@/types'

defineProps<{
  items: Video[] | undefined
}>()
</script>

<template>
  <UBlogPosts class="grid grid-cols-1 gap-x-4 sm:grid-cols-3 lg:gap-y-10">
    <VideoCard
      v-for="(item, index) in items ?? []"
      :key="item.id"
      :item="item"
      :index="index"
    />
  </UBlogPosts>
</template>
