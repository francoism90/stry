<script setup lang="ts">
import { useCollection } from '@/composables/collection'
import type { TagCollection } from '@/types'
import TagCard from './TagCard.vue'

interface Props {
  items: TagCollection | null | undefined
}

defineProps<Props>()

const { orientation } = useCollection()
</script>

<template>
  <UBlogPosts
    :orientation="orientation"
    class="gap-6 lg:gap-y-6"
  >
    <TagCard
      v-for="(item, index) in items?.data || []"
      :key="index"
      :item="item"
    />
  </UBlogPosts>
</template>
