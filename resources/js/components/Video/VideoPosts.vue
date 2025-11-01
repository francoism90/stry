<script setup lang="ts">
import VideoPost from '@/components/Video/VideoPost.vue'
import { useCollection } from '@/composables/collection'
import type { VideoCollection } from '@/types'

interface Props {
  items: VideoCollection | null | undefined
}

defineProps<Props>()

const { orientation } = useCollection()
</script>

<template>
  <UBlogPosts
    id="item-list"
    :orientation="orientation"
  >
    <VideoPost
      v-for="item in items?.data || []"
      :key="item.id"
      :item="item"
    />
  </UBlogPosts>
</template>
