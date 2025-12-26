<script setup lang="ts">
import HomeController from '@/actions/App/Client/Account/Controllers/HomeController'
import type { Tag } from '@/types'

defineProps<{
  items: Tag[] | undefined
}>()

const url = (search: string) => HomeController.url('all', { query: { search } })
</script>

<template>
  <UBlogPosts class="grid-cols-1 gap-x-3 gap-y-2 sm:grid-cols-2 lg:grid-cols-3 lg:gap-y-3">
    <UButton
      v-for="item in items"
      :key="item.id"
      :label="`# ${item.name}`"
      :to="url(item.name)"
      variant="soft"
      size="lg"
      color="secondary"
    />
  </UBlogPosts>
</template>
