<script setup lang="ts">
import { usePlayer } from '@/composables/player'
import { router } from '@inertiajs/vue3'
import type { ButtonProps } from '@nuxt/ui'
import type { MediaPlayer } from 'vidstack'
import 'vidstack/bundle'
import { onBeforeUnmount, onMounted, ref, shallowRef } from 'vue'

const player = shallowRef<MediaPlayer>()
const seeked = ref(false)

const { playlist, progress, store } = usePlayer()

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

const listener = () =>
  player.value?.subscribe(({ canPlay, canSeek, currentTime }) => {
    if (!seeked.value && canPlay && canSeek) {
      if (progress.value && progress.value > 0) {
        player.value!.currentTime = progress.value
      }

      seeked.value = true
    }

    // Store current time periodically (skip first second)
    if (seeked.value && currentTime > 0.1) {
      store(currentTime)
    }

    return () => player.value
  })

onMounted(() => listener())
onBeforeUnmount(() => listener())
</script>

<template>
  <div class="relative w-full flex-1">
    <UEmpty
      v-if="!playlist?.valid"
      title="Preparing your video..."
      :description="`Please wait while we load the video for you. This may take a few moments. (${playlist?.state})`"
      icon="i-lucide-hard-drive-download"
      :actions="actions"
    />

    <media-player
      ref="player"
      v-show="playlist?.valid"
      .src="playlist?.asset || undefined"
      .autoPlay="true"
      .playsInline="true"
      crossOrigin="anonymous"
    >
      <media-video-layout />
      <media-provider />
    </media-player>
  </div>
</template>
