<script setup lang="ts">
import HomeController from '@/actions/App/Client/Account/Controllers/HomeController'
import type { Tag } from '@/types'

defineProps<{
  items: Tag[] | undefined
}>()

const url = (tag: string) => HomeController.url('all', { query: { tag } })
</script>

<template>
  <UBlogPosts class="grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:gap-y-4">
    <UButton
      v-for="item in items"
      :key="item.id"
      :label="`# ${item.name}`"
      :to="url(item.id)"
      variant="soft"
      size="lg"
      color="secondary"
    />
  </UBlogPosts>
</template>
