<script setup lang="ts">
import TagController from '@/actions/App/Client/Tags/Controllers/TagController'
import type { TagCollection } from '@/types'

defineProps<{
  items: TagCollection | undefined
}>()
</script>

<template>
  <UBlogPosts class="grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-2 lg:gap-y-4">
    <UPageCard
      v-for="(item, index) in items?.data"
      :key="index"
      v-bind="item"
      variant="subtle"
      :title="item.name"
      :description="`${item.videos} videos`"
      :to="TagController.url(item.id)"
      :ui="{ container: 'p-4 sm:p-4' }"
    />
  </UBlogPosts>
</template>
