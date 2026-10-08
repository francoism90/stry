<script setup lang="ts">
import TagCard from '@/components/Tags/TagCard.vue'
import type { Tag } from '@/types'

defineProps<{
  items: Tag[] | undefined
}>()
</script>

<template>
  <div class="grid grid-cols-[repeat(auto-fill,minmax(min(13.75rem,100%),1fr))] gap-4">
    <TagCard
      v-for="item in items ?? []"
      :key="item.id"
      :item="item"
    />
  </div>
</template>
