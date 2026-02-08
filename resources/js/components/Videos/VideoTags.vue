<script setup lang="ts">
import HomeController from '@/actions/App/Client/Account/Controllers/HomeController'
import type { Tag } from '@/types'

defineProps<{
  items: Tag[] | undefined
}>()

const url = (tag: string) => HomeController.url('all', { query: { tag } })
</script>

<template>
  <div
    v-if="items?.length"
    class="flex flex-wrap gap-1.5 overflow-auto"
  >
    <UButton
      v-for="item in items"
      :key="item.id"
      :label="item.name"
      :to="url(item.id)"
      variant="subtle"
      size="xs"
      class="z-10"
    />
  </div>
</template>
