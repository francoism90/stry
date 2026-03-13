<script setup lang="ts">
import TagCard from '@/components/Tags/TagCard.vue'
import type { Tag } from '@/types'

defineProps<{
  items: Tag[] | undefined
}>()
</script>

<template>
  <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
    <TagCard
      v-for="item in items"
      :key="item.id"
      :item="item"
    />
  </div>
</template>
