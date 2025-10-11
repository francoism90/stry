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
      items-element="#page-grid"
      :buffer="200"
    >
      <UPageGrid
        id="page-grid"
        class="gap-x-4 gap-y-6 py-2"
      >
        <TagCard
          v-for="item in items.data"
          :key="item.id"
          :item
        />
      </UPageGrid>
    </InfiniteScroll>
  </UPageBody>
</template>
