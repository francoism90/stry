<script setup lang="ts">
import VideoController from '@/actions/App/Client/Videos/Controllers/VideoController'
import type { VideoCollection } from '@/types'

defineProps<{
  items: VideoCollection | undefined
}>()
</script>

<template>
  <UBlogPosts class="grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-2">
    <UBlogPost
      v-for="(item, index) in items?.data"
      variant="naked"
      :key="index"
      :title="item.title"
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
        <p
          v-if="item.description?.length"
          v-html="item.description"
        />

        <div
          v-if="item.tags?.length"
          class="flex flex-wrap gap-x-2 overflow-auto"
        >
          <UButton
            v-for="(tag, index) in item.tags"
            :key="index"
            :label="tag.name"
            variant="outline"
            size="xs"
            class="mt-2"
          />
        </div>
      </template>
    </UBlogPost>
  </UBlogPosts>
</template>
