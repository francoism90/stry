<script setup lang="ts">
import LibraryController from '@/actions/App/Client/Videos/Controllers/LibraryController'
import type { TagCollection } from '@/types'

defineProps<{
  items: TagCollection | undefined
}>()

const url = (search: string) => LibraryController.url({ query: { search } })
</script>

<template>
  <UBlogPosts class="grid-cols-1 gap-x-3 gap-y-1 sm:grid-cols-2 lg:grid-cols-2 lg:gap-y-3">
    <UButton
      v-for="item in items?.data"
      :key="item.id"
      variant="soft"
      size="lg"
      :label="`# ${item.name}`"
      :to="url(item.name)"
    />
  </UBlogPosts>
</template>
