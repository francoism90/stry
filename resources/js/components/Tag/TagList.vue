<script setup lang="ts">
import type { TagCollection } from '@/types'
import TagCard from './TagCard.vue'

interface Props {
  items: TagCollection | null | undefined
  orientation?: 'horizontal' | 'vertical'
}

defineProps<Props>()
</script>

<template>
  <UPageList
    :orientation="orientation || 'vertical'"
    divide
  >
    <TagCard
      v-for="(item, index) in items?.data || []"
      :key="index"
      :item="item"
    />
  </UPageList>
</template>
