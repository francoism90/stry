<script setup lang="ts">
import { show } from '@/actions/App/Web/Tags/Controllers/TagController'
import type { Tag } from '@/types'

defineProps<{
  items: Tag[] | undefined
}>()
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
        :to="show.url(item.id)"
        active-class="text-neutral"
        class="hover:text-highlighted z-10 transition-colors"
      >
        {{ item.name }}
      </ULink>
    </template>
  </div>
</template>
