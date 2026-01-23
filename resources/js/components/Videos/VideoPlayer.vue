<script setup lang="ts">
import { useShaka } from '@/composables/shaka'
import { router } from '@inertiajs/vue3'
import type { ButtonProps } from '@nuxt/ui'
import { ref } from 'vue'

const videoElement = ref<HTMLVideoElement>()

const { ready, error } = useShaka(videoElement)

const actions = ref<ButtonProps[]>([
  {
    icon: 'i-lucide-refresh-cw',
    label: 'Refresh',
    color: 'neutral',
    size: 'sm',
    variant: 'subtle',
    onClick: () => router.reload({ only: ['playlist, progress'] }),
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
  <div class="relative w-full flex-1">
    <UEmpty
      v-if="!ready"
      title="Preparing your video..."
      description="Please wait while we get everything ready."
      icon="i-lucide-hard-drive-download"
      :actions="actions"
    />

    <UEmpty
      v-else-if="error"
      title="Playback Error"
      :description="error.message || 'An error occurred during video playback.'"
      icon="i-lucide-alert-circle"
      :actions="actions"
    />

    <video
      v-show="ready && !error"
      ref="videoElement"
      class="aspect-video max-h-[50vh] w-full rounded-sm sm:max-h-[60vh] lg:max-h-[70vh]"
      controls
      autoplay
      playsinline
      crossorigin="anonymous"
    />
  </div>
</template>
