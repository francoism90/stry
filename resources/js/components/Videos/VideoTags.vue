<script setup lang="ts">
import type { Tag } from '@/types'

defineProps<{
  items: Tag[] | undefined
}>()

const url = (tag: string) => `/all?tag=${tag}`
</script>

<template>
  <div
    v-if="items?.length"
    class="text-muted flex flex-wrap items-center gap-x-1 gap-y-0.5 text-sm"
  >
    <template
      v-for="(item, index) in items"
      :key="item.id"
    >
      <span
        v-if="index > 0"
        class="opacity-40 select-none"
        >·</span
      >

      <ULink
        :to="url(item.id)"
        class="hover:text-highlighted z-10 transition-colors"
      >
        {{ item.name }}
      </ULink>
    </template>
  </div>
</template>
