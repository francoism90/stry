<script setup lang="ts">
import { useShaka } from '@/composables/shaka'
import { router } from '@inertiajs/vue3'
import type { ButtonProps } from '@nuxt/ui'
import { onMounted, ref } from 'vue'

const el = ref<HTMLMediaElement | undefined>()

const { initialize, ready, error } = useShaka(el)

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

onMounted(() => initialize())
</script>

<template>
  <div class="relative w-full flex-1">
    <UEmpty
      v-if="!ready && !error"
      title="Preparing your video..."
      description="Please wait while we get everything ready."
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

    <video
      ref="el"
      v-show="ready && !error"
      class="bg-default/90 aspect-video max-h-[50vh] w-full rounded-lg sm:max-h-[60vh] lg:max-h-[70vh]"
      controls
      autoplay
      playsinline
      preload="metadata"
      crossorigin="anonymous"
    />
  </div>
</template>
