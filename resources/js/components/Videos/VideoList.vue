<script setup lang="ts">
import VideoController from '@/actions/App/Client/Videos/Controllers/VideoController'
import type { VideoCollection } from '@/types'

defineProps<{
  items: VideoCollection | undefined
}>()
</script>

<template>
  <UBlogPosts>
    <UBlogPost
      v-for="item in items?.data"
      :key="item.id"
      variant="naked"
      :title="item.title"
      :image="item.thumb"
      :badge="item.timestamp"
      :date="item.released_at || item.published_at || item.created_at"
      :to="VideoController.url(item.id)"
      :ui="{
        root: 'gap-y-4 rounded-none',
        header: 'rounded-md shadow-none',
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
            v-for="tag in item.tags"
            :key="tag.id"
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
