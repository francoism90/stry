<script setup lang="ts">
import type { TagCollection } from '@/types'
import TagCard from './TagCard.vue'

interface Props {
  items?: TagCollection
  orientation?: 'horizontal' | 'vertical'
}

defineProps<Props>()
</script>

<template>
  <UBlogPosts
    id="page-grid"
    :orientation="orientation || 'vertical'"
  >
    <TagCard
      v-for="(item, index) in items?.data || []"
      :key="index"
      :item="item"
    />
  </UBlogPosts>
</template>
