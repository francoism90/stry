<script setup lang="ts">
import { show } from '@/actions/App/Web/Videos/Controllers/VideoController'
import VideoTags from '@/components/Videos/VideoTags.vue'
import type { Video } from '@/types'

defineProps<{
  item: Video
}>()
</script>

<template>
  <UBlogPost
    variant="naked"
    :title="item.title"
    :date="item.released ?? undefined"
    :to="show.url(item.id)"
    :ui="{
      root: 'gap-y-3 rounded-none',
      title: 'line-clamp-2 text-sm leading-snug font-medium capitalize',
      date: 'sr-only',
      body: 'p-0 sm:p-0 lg:px-0',
      description: 'mt-0.5 flex flex-col gap-2',
    }"
  >
    <template #header>
      <div class="relative overflow-hidden rounded-lg">
        <img
          v-if="item.thumb"
          :src="item.thumb"
          :srcset="item.thumb_srcset ?? undefined"
          :alt="item.title"
          class="aspect-video w-full object-cover"
          loading="lazy"
          decoding="auto"
        />

        <div
          v-else
          class="aspect-video w-full bg-muted"
        />

        <div class="absolute inset-x-0 bottom-0 flex items-end justify-between p-2">
          <UBadge
            v-if="item.captioned"
            label="CC"
            color="neutral"
            variant="solid"
            size="xs"
            class="bg-black/70 text-white backdrop-blur-sm"
            title="Closed captions available"
          />

          <span class="ml-auto" />

          <UBadge
            v-if="item.timestamp"
            :label="item.timestamp"
            color="neutral"
            variant="solid"
            size="xs"
            class="bg-black/70 text-white backdrop-blur-sm"
          />
        </div>
      </div>
    </template>

    <template #description>
      <VideoTags :items="item.tags" />
    </template>
  </UBlogPost>
</template>
