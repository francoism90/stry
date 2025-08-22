<script setup lang="ts">
import { usePlayer } from '@/composables/player'
import { useThrottleFn } from '@vueuse/core'
import type { MediaPlayer } from 'vidstack'
import 'vidstack/bundle'
import { onBeforeUnmount, onMounted, ref, shallowRef } from 'vue'
import PageSection from '../Ui/PageSection.vue'

const player = shallowRef<MediaPlayer>()
const seeked = ref(false)

const { src, captions, progress, record } = usePlayer()

const listener = () => {
  const debouncedRecord = useThrottleFn((time: number | null) => record(time), 3500)

  return player.value?.subscribe(({ canSeek, currentTime }) => {
    if (canSeek && !seeked.value) {
      player.value!.currentTime = progress.value || 0
      seeked.value = true
    }

    if (seeked.value && currentTime > 0 && progress.value != currentTime) {
      debouncedRecord(currentTime)
    }

    return () => player.value
  })
}

onMounted(() => listener())
onBeforeUnmount(() => listener())
</script>

<template>
  <PageSection>
    <div
      v-if="!src"
      class="grid min-h-52 w-full place-items-center rounded-xl bg-neutral-800 md:min-h-96"
    >
      <span class="text-muted">Waiting for video to be ready...</span>
    </div>

    <media-player
      ref="player"
      .src="src || undefined"
      .playsInline="true"
      .autoPlay="true"
      crossOrigin="anonymous"
      class="rounded-xl"
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
  </PageSection>
</template>
