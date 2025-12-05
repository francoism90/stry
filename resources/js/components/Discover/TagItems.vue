<script setup lang="ts">
import type { TagCollection } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'

defineProps<{
  items: TagCollection
}>()
</script>

<template>
  <InfiniteScroll data="items">
    <UBlogPosts class="gap-10 px-4 py-4 sm:grid-cols-2 sm:px-6 lg:grid-cols-2">
      <UPageCard
        v-for="(item, index) in items.data"
        :key="index"
        :ui="{ container: 'p-2 sm:p-4' }"
        v-bind="item"
        :title="item.name"
        :description="`${item.category} • ${item.videos} videos`"
      />
    </UBlogPosts>
  </InfiniteScroll>
</template>
