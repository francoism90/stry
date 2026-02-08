<script setup lang="ts">
import HomeController from '@/actions/App/Client/Account/Controllers/HomeController'
import type { Tag } from '@/types'

defineProps<{
  items: Tag[] | undefined
}>()

const url = (tag: string) => HomeController.url('all', { query: { tag } })
</script>

<template>
  <UBlogPosts class="grid-cols-1 gap-3 gap-y-3 sm:grid-cols-2 lg:grid-cols-3 lg:gap-y-3">
    <UButton
      v-for="item in items"
      :key="item.id"
      :label="`${item.name} (${item.videos ?? 0})`"
      :to="url(item.id)"
      variant="soft"
      size="lg"
      color="neutral"
    />
  </UBlogPosts>
</template>
