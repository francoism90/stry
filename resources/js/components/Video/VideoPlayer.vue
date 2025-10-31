<script setup lang="ts">
import { usePlayer } from '@/composables/player'
import type { MediaPlayer } from 'vidstack'
import 'vidstack/bundle'
import { onBeforeUnmount, onMounted, ref, shallowRef } from 'vue'

const player = shallowRef<MediaPlayer>()
const seeked = ref(false)

const { src, captions, progress, store } = usePlayer()

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
  <media-player
    ref="player"
    .src="src || undefined"
    .autoPlay="true"
    .playsInline="true"
    crossOrigin="anonymous"
    class="rounded-xl"
  >
    <media-video-layout />
    <media-provider>
      <template v-if="captions?.length">
        <track
          v-for="caption in captions"
          :key="caption.id"
          :src="caption.asset"
          :label="caption.name"
          kind="captions"
        />
      </template>
    </media-provider>
  </media-player>
</template>
