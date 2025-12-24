<script setup lang="ts">
import type { TagCollection } from '@/types'

defineProps<{
  items: TagCollection | undefined
}>()
</script>

<template>
  <UBlogPosts class="grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-2 lg:grid-cols-2 lg:gap-y-8">
    {{ items?.data }}

    <UPageCard
      v-for="item in items?.data"
      :key="item.id"
      variant="subtle"
      :title="item.name"
      :ui="{ container: 'p-4 sm:p-4' }"
    />
  </UBlogPosts>
</template>
