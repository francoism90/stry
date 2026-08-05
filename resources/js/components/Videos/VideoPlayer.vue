<script setup lang="ts">
import { useShaka } from '@/composables/shaka'
import type { Playlist, Video } from '@/types'
import { router } from '@inertiajs/vue3'
import type { ButtonProps } from '@nuxt/ui'
import { ref } from 'vue'

const props = defineProps<{
  video?: Video | undefined
  playlist?: Playlist | undefined
  progress?: number | undefined
}>()

const container = ref<HTMLDivElement | undefined>()
const media = ref<HTMLMediaElement | undefined>()

const { ready, error } = useShaka(container, media, props.video, props.playlist, props.progress)

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
      icon="i-lucide-hard-drive-download"
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
      class="aspect-video max-h-[35dvh] w-full [clip-path:inset(0_round_0.5rem)] md:max-h-[50dvh] lg:max-h-[60dvh] fullscreen:aspect-auto fullscreen:max-h-none fullscreen:[clip-path:inset(0)]"
    >
      <video
        ref="media"
        class="size-full bg-black"
        crossorigin="anonymous"
        preload="metadata"
        playsinline
        autoplay
      />
    </div>
  </figure>
</template>

<style lang="postcss">
.shaka-seek-bar-container {
  @apply top-1.25 mb-1 h-1.25 border-t-8 border-b-8;
}

.shaka-seek-button {
  @apply hidden cursor-pointer items-center justify-center gap-0.5 border-0 bg-transparent px-1.5 py-1 text-inherit opacity-90 transition-opacity duration-150 lg:inline-flex;
}
</style>
