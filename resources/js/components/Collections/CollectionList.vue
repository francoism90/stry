<script setup lang="ts">
import CollectionCard from '@/components/Collections/CollectionCard.vue'
import type { Group } from '@/types'

defineProps<{
  items: Group[] | undefined
}>()
</script>

<template>
  <UBlogPosts class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
    <CollectionCard
      v-for="item in items"
      :key="item.id"
      :item="item"
    />
  </UBlogPosts>
</template>
