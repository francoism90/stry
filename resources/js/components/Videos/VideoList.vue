<script setup lang="ts">
import VideoController from '@/actions/App/Client/Videos/Controllers/VideoController'
import type { VideoCollection } from '@/types'

defineProps<{
  items: VideoCollection
}>()
</script>

<template>
  <UBlogPosts class="grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-2">
    <UBlogPost
      v-for="(item, index) in items.data"
      v-bind="item"
      variant="naked"
      :key="index"
      :image="item.thumb"
      :badge="item.timestamp"
      :date="item.released_at || item.published_at || item.created_at"
      :to="VideoController.url(item.id)"
      :ui="{
        root: 'gap-y-4',
        header: 'rounded-xs shadow-none',
        body: 'p-0 sm:p-0 lg:px-0',
        title: 'font-serif text-sm',
        description: 'text-sm',
      }"
    >
      <template #description>
        <p v-html="item.description" />

        <div
          v-if="item.tags?.length"
          class="mt-4 flex flex-wrap gap-2 overflow-auto"
        >
          <UButton
            v-for="(tag, index) in item.tags"
            :key="index"
            :label="tag.name"
            variant="soft"
            size="xs"
          />
        </div>
      </template>
    </UBlogPost>
  </UBlogPosts>
</template>
