<script setup lang="ts">
import { useVideo } from '@/composables/video'
import { show } from '@/routes/videos'
import type { Video } from '@/types'
import { ref } from 'vue'
import VideoTags from './VideoTags.vue'

const props = defineProps<{
  item: Video
  index?: number
  label?: string
}>()

const isAboveFold = (props.index ?? 0) < 9
const isLcp = props.index === 0

const { toggleSave } = useVideo()

const isSaved = ref(props.item.saved ?? false)

/**
 * Saves the video without reloading the list, so the infinite scroll keeps its pages.
 */
const save = (): void => {
  isSaved.value = !isSaved.value
  toggleSave(props.item, ['collections'])
}
</script>

<template>
  <UBlogPost
    variant="naked"
    :title="item.title"
    :date="item.released ?? undefined"
    :to="show.url(item.id)"
    :ui="{
      root: 'group/card gap-y-1.5 rounded-none',
      title: 'line-clamp-2 text-sm leading-snug font-medium capitalize',
      date: 'sr-only',
      body: 'p-0 sm:p-0 lg:px-0',
      description: 'mt-0.5 flex flex-col gap-2',
    }"
  >
    <template #header>
      <div class="relative overflow-hidden rounded-lg bg-muted">
        <img
          v-if="item.thumb"
          :src="item.thumb"
          :srcset="item.thumb_srcset ?? undefined"
          :alt="item.title"
          class="aspect-video w-full object-cover"
          :loading="isAboveFold ? 'eager' : 'lazy'"
          :fetchpriority="isLcp ? 'high' : 'auto'"
          decoding="auto"
        />

        <div
          v-else
          class="aspect-video w-full"
        />

        <div
          class="pointer-events-none absolute inset-0 grid place-items-center bg-linear-to-b from-black/55 via-black/10 via-45% to-black/35 opacity-0 transition-opacity duration-200 group-hover/card:opacity-100"
          aria-hidden="true"
        >
          <span
            class="grid size-13 place-items-center rounded-full border border-white/28 bg-white/16 text-white backdrop-blur-lg backdrop-saturate-160"
          >
            <UIcon
              name="i-lucide-play"
              class="ms-0.5 size-5.5 fill-current"
            />
          </span>
        </div>

        <UButton
          v-if="item.saved !== null"
          :icon="isSaved ? 'i-lucide-bookmark-check' : 'i-lucide-bookmark-plus'"
          :aria-label="isSaved ? `Unsave ${item.title}` : `Save ${item.title}`"
          :aria-pressed="isSaved"
          color="neutral"
          variant="solid"
          class="pointer-events-auto absolute end-2 top-2 z-10 rounded-full border border-white/20 bg-black/45 text-white opacity-0 backdrop-blur-lg backdrop-saturate-160 transition-opacity group-hover/card:opacity-100 hover:bg-black/60 focus-visible:opacity-100"
          :class="{ 'text-primary': isSaved }"
          @click.prevent="save"
        />

        <div class="absolute inset-x-0 bottom-0 flex items-end justify-between p-2">
          <UBadge
            v-if="item.captioned"
            label="CC"
            color="neutral"
            variant="solid"
            size="sm"
            class="bg-black/70 text-white backdrop-blur-sm"
            title="Closed captions available"
          />

          <span class="ml-auto" />

          <UBadge
            v-if="item.timestamp"
            :label="item.timestamp"
            color="neutral"
            variant="solid"
            size="sm"
            class="bg-black/70 text-white tabular-nums backdrop-blur-sm"
          />
        </div>
      </div>
    </template>

    <template #description>
      <span
        v-if="label"
        class="text-sm text-primary"
      >
        {{ label }}
      </span>

      <VideoTags :items="item.tags" />
    </template>
  </UBlogPost>
</template>
