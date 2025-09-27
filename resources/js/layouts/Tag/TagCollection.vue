<script setup lang="ts">
import TagCard from '@/components/Tag/TagCard.vue'
import type { TagCollection } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'

interface Props {
  items: TagCollection
}

defineProps<Props>()
</script>

<template>
  <UPageBody>
    <slot />

    <InfiniteScroll
      data="items"
      :buffer="200"
    >
      <UPageGrid class="gap-4">
        <TagCard
          v-for="item in items.data"
          :key="item.id"
          :item
        />
      </UPageGrid>
    </InfiniteScroll>
  </UPageBody>
</template>
