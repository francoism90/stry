<script setup lang="ts">
import type { TagCollection } from '@/types'
import TagCard from './TagCard.vue'

interface Props {
  items: TagCollection | null | undefined
  orientation?: 'horizontal' | 'vertical' | undefined
}

defineProps<Props>()
</script>

<template>
  <UBlogPosts
    id="item-list"
    :orientation="orientation"
    class="gap-6 lg:gap-y-6"
  >
    <TagCard
      v-for="item in items?.data || []"
      :key="item.id"
      :item="item"
    />
  </UBlogPosts>
</template>
