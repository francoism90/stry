<script setup lang="ts">
import { usePlayer } from '@/composables/player'
import type { MediaPlayer } from 'vidstack'
import 'vidstack/bundle'
import { onBeforeUnmount, onMounted, ref, shallowRef } from 'vue'

const player = shallowRef<MediaPlayer>()
const seeked = ref(false)

const { state, video, progress, store } = usePlayer()

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
  <div class="relative w-full rounded-xl">
    <media-player
      ref="player"
      .src="state?.asset || undefined"
      .autoPlay="true"
      .playsInline="true"
      crossOrigin="anonymous"
      class="rounded-xl"
    >
      <media-video-layout />
      <media-provider>
        <template v-if="video?.captions?.length">
          <track
            v-for="caption in video.captions"
            :key="caption.id"
            :src="caption.asset"
            :label="caption.name"
            kind="captions"
          />
        </template>
      </media-provider>
    </media-player>

    <div v-if="!state?.valid">
      <UAlert
        color="neutral"
        variant="soft"
        title="Preparing your video..."
        description="Please wait while we load the video for you."
      />
    </div>
  </div>
</template>
