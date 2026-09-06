<script setup lang="ts">
import type { Chapter } from '@/types'
import { formatDuration } from '@/utils/duration'
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

const props = defineProps<{
  container?: HTMLElement | undefined
  chapters?: Chapter[] | undefined
  duration?: number | null | undefined
  seek: (time: number) => void
}>()

const isOpen = ref(false)
const seekBar = ref<HTMLElement | undefined>()

let observer: MutationObserver | undefined

// Shaka's UI overlay renders its seek bar asynchronously into `container` with no public API for
// markers, so we locate the DOM node it produces and teleport our own overlay into it. This
// couples to an internal class name (stable across recent Shaka releases, but not a public
// contract) - if a future Shaka UI version renames it, findSeekBar() simply stops finding a
// target and the tick marks silently stop rendering; the chapter list button below is unaffected.
function findSeekBar(): void {
  seekBar.value = props.container?.querySelector<HTMLElement>('.shaka-seek-bar-container') ?? undefined
}

watch(
  () => props.container,
  (container) => {
    observer?.disconnect()
    seekBar.value = undefined

    if (!container) {
      return
    }

    findSeekBar()

    if (!seekBar.value) {
      observer = new MutationObserver(() => {
        findSeekBar()

        if (seekBar.value) {
          observer?.disconnect()
        }
      })

      observer.observe(container, { childList: true, subtree: true })
    }
  },
  { immediate: true },
)

onBeforeUnmount(() => observer?.disconnect())

const chapterOffset = (chapter: Chapter): string =>
  `${((chapter.start_time / (props.duration || 1)) * 100).toFixed(2)}%`

const sortedChapters = computed<Chapter[]>(() =>
  [...(props.chapters ?? [])].sort((a, b) => a.start_time - b.start_time),
)

const seekTo = (chapter: Chapter): void => {
  props.seek(chapter.start_time)
  isOpen.value = false
}

onMounted(findSeekBar)
</script>

<template>
  <div v-if="sortedChapters.length">
    <Teleport
      v-if="seekBar"
      :to="seekBar"
    >
      <div class="pointer-events-none absolute inset-0 z-10">
        <div
          v-for="chapter in sortedChapters"
          :key="chapter.id"
          class="absolute inset-y-0 w-px bg-white/70"
          :style="{ left: chapterOffset(chapter) }"
        />
      </div>
    </Teleport>

    <UPopover v-model:open="isOpen">
      <UButton
        icon="i-lucide-list-video"
        label="Chapters"
        color="neutral"
        variant="solid"
        size="lg"
        class="absolute top-4 right-4 z-10"
      />

      <template #content>
        <UPageList class="max-h-64 w-64 overflow-y-auto p-1">
          <UButton
            v-for="chapter in sortedChapters"
            :key="chapter.id"
            :label="chapter.label"
            color="neutral"
            variant="ghost"
            block
            class="justify-between"
            @click="seekTo(chapter)"
          >
            <template #trailing>
              <span class="text-xs text-muted">{{ formatDuration(chapter.start_time) }}</span>
            </template>
          </UButton>
        </UPageList>
      </template>
    </UPopover>
  </div>
</template>
