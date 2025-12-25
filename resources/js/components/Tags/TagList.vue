<script setup lang="ts">
import HomeController from '@/actions/App/Client/Account/Controllers/HomeController'
import type { TagCollection } from '@/types'

defineProps<{
  items: TagCollection | undefined
}>()

const url = (search: string) => HomeController.url('default', { query: { search } })
</script>

<template>
  <UBlogPosts class="grid-cols-1 gap-x-3 gap-y-2 sm:grid-cols-2 lg:grid-cols-3 lg:gap-y-3">
    <UButton
      v-for="item in items?.data"
      :key="item.id"
      variant="soft"
      size="lg"
      color="secondary"
      :label="`# ${item.name}`"
      :to="url(item.name)"
    />
  </UBlogPosts>
</template>
