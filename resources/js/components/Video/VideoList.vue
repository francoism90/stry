<script setup lang="ts">
import VideoCard from '@/components/Video/VideoCard.vue'
import type { Video } from '@/types'
import { Deferred, router, WhenVisible } from '@inertiajs/vue3'
import { computed } from 'vue'

interface Props {
  results: Video[]
  currentPage: number
  nextPage: string | null
}

const props = defineProps<Props>()

const fetchable = computed(() => !!props.nextPage)

const fetch = () =>
  router.visit(props.nextPage ?? '/', {
    replace: true,
    preserveScroll: true,
    preserveState: true,
  })
</script>

<template>
  <Deferred data="items">
    <template #fallback>
      <div>Loading items...</div>
    </template>

    <div class="grid grid-cols-1 gap-4 py-2 sm:grid-cols-2 md:grid-cols-3">
      <VideoCard
        v-for="item in results"
        :key="item.id"
        :item
      />
    </div>

    <WhenVisible
      :always="fetchable"
      :params="{
        only: ['items'],
        data: fetchable ? { page: currentPage + 1 } : undefined,
      }"
    >
      <template #fallback>
        <div class="sr-only">Loading more...</div>
      </template>

      <div
        v-if="fetchable"
        class="flex h-20 w-full items-center justify-center"
      >
        <UButton
          label="Load more"
          variant="soft"
          @click="fetch"
        />
      </div>
    </WhenVisible>
  </Deferred>
</template>
