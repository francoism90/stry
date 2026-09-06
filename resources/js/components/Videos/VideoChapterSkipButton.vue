<script setup lang="ts">
import type { Chapter } from '@/types'
import { computed } from 'vue'

const props = defineProps<{
  chapters?: Chapter[] | undefined
  currentTime: number
  seek: (time: number) => void
}>()

const activeChapter = computed<Chapter | undefined>(() =>
  props.chapters?.find(
    (chapter) => chapter.skippable && props.currentTime >= chapter.start_time && props.currentTime < chapter.end_time,
  ),
)

const skip = (): void => {
  if (activeChapter.value) {
    props.seek(activeChapter.value.end_time)
  }
}
</script>

<template>
  <UButton
    v-if="activeChapter"
    :label="`Skip ${activeChapter.type}`"
    icon="i-lucide-skip-forward"
    color="neutral"
    variant="solid"
    size="lg"
    class="absolute right-4 bottom-20 z-10 capitalize"
    @click.stop.prevent="skip"
  />
</template>
