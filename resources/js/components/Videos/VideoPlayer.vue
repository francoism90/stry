<script setup lang="ts">
import { usePlayer } from '@/composables/player'
import { useShaka } from '@/composables/shaka'
import { router } from '@inertiajs/vue3'
import type { ButtonProps } from '@nuxt/ui'
import { ref } from 'vue'

const videoElement = ref<HTMLVideoElement>()
const seeked = ref(false)

const { playlist, progress, store } = usePlayer()
const { loading, error } = useShaka(videoElement, playlist)

const actions = ref<ButtonProps[]>([
  {
    icon: 'i-lucide-refresh-cw',
    label: 'Refresh',
    color: 'neutral',
    size: 'sm',
    variant: 'subtle',
    onClick: () => router.reload({ only: ['playlist'] }),
  },
  {
    icon: 'i-lucide-flag',
    label: 'Report',
    color: 'neutral',
    size: 'sm',
    variant: 'subtle',
  },
])

const handleTimeUpdate = () => {
  if (!videoElement.value) return

  const currentTime = videoElement.value.currentTime

  // Seek to saved progress on first play
  if (!seeked.value && videoElement.value.readyState >= 2 && progress.value && progress.value > 0) {
    videoElement.value.currentTime = progress.value
    seeked.value = true
  }

  // Store current time periodically (skip first second)
  if (seeked.value && currentTime > 0.1) {
    store(currentTime)
  }
}
</script>

<template>
  <div class="relative w-full flex-1">
    <UEmpty
      v-if="loading || !playlist?.valid"
      title="Preparing your video..."
      description="Please wait while we get everything ready."
      icon="i-lucide-hard-drive-download"
      :actions="actions"
    />

    <UEmpty
      v-else-if="error"
      title="Playback Error"
      :description="error"
      icon="i-lucide-alert-circle"
      :actions="actions"
    />

    <video
      ref="videoElement"
      v-show="playlist?.valid && !loading && !error"
      class="aspect-video max-h-[50vh] w-full rounded-sm sm:max-h-[60vh] lg:max-h-[70vh]"
      controls
      autoplay
      playsinline
      crossorigin="anonymous"
      @timeupdate="handleTimeUpdate"
    />
  </div>
</template>
