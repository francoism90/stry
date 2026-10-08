<script setup lang="ts">
import VideoCard from '@/components/Videos/VideoCard.vue'
import type { Video } from '@/types'

defineProps<{
  items: Video[] | undefined
  leadLabel?: string
}>()
</script>

<template>
  <UBlogPosts
    orientation="vertical"
    class="grid grid-cols-[repeat(auto-fill,minmax(min(16.25rem,100%),1fr))] gap-x-4 gap-y-10 lg:gap-y-10"
  >
    <VideoCard
      v-for="(item, index) in items ?? []"
      :key="item.id"
      :item="item"
      :index="index"
      :label="index === 0 ? leadLabel : undefined"
    />
  </UBlogPosts>
</template>
