<script setup lang="ts">
import VideoController from '@/actions/App/Client/Videos/Controllers/VideoController'
import VideoTags from '@/components/Videos/VideoTags.vue'
import type { Video } from '@/types'

defineProps<{
  items: Video[] | undefined
}>()
</script>

<template>
  <UBlogPosts class="gap-3 gap-y-9 lg:gap-y-12">
    <UBlogPost
      v-for="item in items"
      :key="item.id"
      variant="naked"
      :title="item.title"
      :image="item.thumb"
      :badge="item.timestamp"
      :date="item.released_at ?? item.published_at ?? item.created_at"
      :to="VideoController.url(item.id)"
      :ui="{
        root: 'gap-y-4 rounded-none',
        header: 'rounded-lg shadow-none',
        title: 'font-serif text-xs',
        date: 'text-xs',
        body: 'p-0 sm:p-0 lg:px-0',
        description: 'mt-2.5 flex flex-col gap-2 text-xs',
      }"
    >
      <template #description>
        <p
          v-if="item.description?.length"
          v-html="item.description"
        />

        <VideoTags :items="item.tags" />
      </template>
    </UBlogPost>
  </UBlogPosts>
</template>
