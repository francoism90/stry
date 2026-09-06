<script setup lang="ts">
import { useShaka } from '@/composables/shaka'
import type { Playlist, Video } from '@/types'
import { router } from '@inertiajs/vue3'
import type { ButtonProps } from '@nuxt/ui'
import { ref } from 'vue'
import VideoChapterSkipButton from '@/components/Videos/VideoChapterSkipButton.vue'

const props = defineProps<{
  video?: Video | undefined
  playlist?: Playlist | undefined
  progress?: number | undefined
}>()

const container = ref<HTMLDivElement | undefined>()
const element = ref<HTMLMediaElement | undefined>()

const { ready, error, media, currentTime } = useShaka(
  container,
  element,
  () => props.video ?? null,
  () => props.playlist ?? null,
  () => props.progress ?? null,
)

const seek = (time: number): void => {
  if (media.value) {
    media.value.currentTime = time
  }
}

const actions = ref<ButtonProps[]>([
  {
    icon: 'i-lucide-refresh-cw',
    label: 'Refresh',
    color: 'neutral',
    size: 'sm',
    variant: 'subtle',
    onClick: () => router.reload({ only: ['playlist', 'progress'] }),
  },
  {
    icon: 'i-lucide-flag',
    label: 'Report',
    color: 'neutral',
    size: 'sm',
    variant: 'subtle',
  },
])
</script>

<template>
  <figure>
    <UEmpty
      v-if="!ready && !error"
      title="Preparing your video..."
      description="This will refresh automatically once your video is ready."
      loading
      :actions="actions"
    />

    <UEmpty
      v-if="error"
      title="Playback Error"
      :description="error.message || 'An error occurred during video playback.'"
      icon="i-lucide-alert-circle"
      :actions="actions"
    />

    <div
      ref="container"
      v-show="ready && !error"
      class="relative aspect-video max-h-[35dvh] w-full [clip-path:inset(0_round_0.5rem)] md:max-h-[50dvh] lg:max-h-[60dvh] fullscreen:aspect-auto fullscreen:max-h-none fullscreen:[clip-path:inset(0)]"
    >
      <video
        ref="element"
        class="size-full bg-black"
        crossorigin="anonymous"
        preload="metadata"
        playsinline
        autoplay
      />

      <VideoChapterSkipButton
        :chapters="video?.chapters"
        :current-time="currentTime"
        :seek="seek"
      />
    </div>
  </figure>
</template>
