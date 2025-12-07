<script setup lang="ts">
import { usePlayer } from '@/composables/player'
import { router } from '@inertiajs/vue3'
import type { ButtonProps } from '@nuxt/ui'
import type { MediaPlayer } from 'vidstack'
import 'vidstack/bundle'
import { onBeforeUnmount, onMounted, ref, shallowRef } from 'vue'

const player = shallowRef<MediaPlayer>()
const seeked = ref(false)

const { state, video, progress, store } = usePlayer()

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
  player.value?.subscribe(({ canSeek, currentTime }) => {
    // Seek to the stored progress time only once
    if (!seeked.value && canSeek) {
      player.value!.currentTime = progress.value || 0
      seeked.value = true
    }

    // Store the current time periodically
    if (seeked.value && currentTime > 0) {
      store(currentTime)
    }

    return () => player.value
  })

onMounted(() => listener())
onBeforeUnmount(() => listener())
</script>

<template>
  <div class="w-full">
    <UEmpty
      v-if="!state"
      title="Preparing your video..."
      description="Please wait while we load the video for you. This may take a few moments."
      icon="i-lucide-hard-drive-download"
      :actions="actions"
    />

    <media-player
      ref="player"
      v-show="state"
      .src="state?.asset || undefined"
      .autoPlay="true"
      .playsInline="true"
      crossOrigin="anonymous"
    >
      <media-video-layout />
      <media-provider>
        <template v-if="video?.captions?.length">
          <track
            v-for="caption in video.captions"
            :key="caption.id"
            :src="caption.asset"
            :label="caption.name"
          />
        </template>
      </media-provider>
    </media-player>
  </div>
</template>
