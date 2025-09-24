<script setup lang="ts">
import MediaCard from '@/components/Media/MediaCard.vue'
import { usePagination } from '@/composables/pagination'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import ResourceLayout from '@/layouts/Video/VideoResource.vue'
import type { MediaCollection, Video } from '@/types'
import { Deferred, router, usePage, WhenVisible } from '@inertiajs/vue3'

interface Props {
  video: Video
  items?: MediaCollection
}

defineOptions({ layout: [DefaultLayout, ResourceLayout] })

defineProps<Props>()

const { hasPages, nextPage } = usePagination()

const fetch = async () => router.get(usePage().props.path, { page: nextPage.value })
</script>

<template>
  <Deferred data="items">
    <template #fallback>
      <div class="sr-only">Loading items...</div>
    </template>

    <UPageGrid
      v-if="items?.data?.length"
      class="gap-3"
    >
      <MediaCard
        v-for="item in items.data"
        :key="item.id"
        :item
      />
    </UPageGrid>

    <WhenVisible
      :always="hasPages"
      :buffer="200"
      :params="{
        only: ['items'],
        data: hasPages ? { page: nextPage } : {},
      }"
    >
      <template #fallback>
        <div class="sr-only">Loading more...</div>
      </template>

      <div
        v-if="hasPages"
        class="flex h-12 w-full items-center justify-center"
      >
        <UButton
          label="Load more"
          variant="soft"
          loading-auto
          @click.prevent="fetch"
        />
      </div>
    </WhenVisible>
  </Deferred>
</template>
