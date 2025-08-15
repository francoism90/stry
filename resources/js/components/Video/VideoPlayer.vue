<script setup lang="ts">
import { usePlayer } from '@/composables/player'
import { useThrottleFn } from '@vueuse/core'
import type { MediaPlayer } from 'vidstack'
import 'vidstack/bundle'
import { onBeforeUnmount, onMounted, ref, shallowRef } from 'vue'

const player = shallowRef<MediaPlayer>()
const seeked = ref(false)

const { src, captions, starts, watchtime } = usePlayer()

const listener = () => {
  const debouncedRecord = useThrottleFn((time: number) => watchtime(time), 5000)

  return player.value?.subscribe(({ canSeek, currentTime }) => {
    if (canSeek && !seeked.value) {
      player.value!.currentTime = starts.value || 0
      seeked.value = true
    }

    if (canSeek && currentTime >= 0) {
      debouncedRecord(currentTime)
    }

    return () => player.value
  })
}

onMounted(() => listener())
onBeforeUnmount(() => listener())
</script>

<template>
  <media-player
    ref="player"
    .src="src || undefined"
    .playsInline="true"
    .autoPlay="true"
    crossOrigin="anonymous"
    class="default-video max-h-64 rounded-xl sm:max-h-96 lg:max-h-2/5"
  >
    <media-video-layout />
    <media-provider>
      <track
        v-for="caption in captions"
        :key="caption.id"
        :src="caption.asset"
        :label="caption.name || 'undefined'"
        kind="captions"
      />
    </media-provider>
  </media-player>
</template>
