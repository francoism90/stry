<script setup lang="ts">
import PageSection from '@/components/Ui/PageSection.vue'
import { usePlayer } from '@/composables/player'
import { useThrottleFn } from '@vueuse/core'
import type { MediaPlayer } from 'vidstack'
import 'vidstack/bundle'
import { onBeforeUnmount, onMounted, ref, shallowRef } from 'vue'

const player = shallowRef<MediaPlayer>()
const seeked = ref(false)

const { state, src, captions, progress, record } = usePlayer()

const sessionHandler = useThrottleFn((time: number | null) => record(time), 2500)

const listener = () =>
  player.value?.subscribe(({ canSeek, currentTime }) => {
    if (!seeked.value && canSeek) {
      player.value!.currentTime = progress.value || 0
      seeked.value = true
    }

    if (seeked.value && currentTime !== undefined) {
      sessionHandler(Math.round(currentTime * 100) / 100)
    }

    return () => player.value
  })

onMounted(() => listener())
onBeforeUnmount(() => listener())
</script>

<template>
  <PageSection>
    <div
      v-if="!src"
      class="grid min-h-52 w-full place-items-center rounded-xl bg-neutral-800 md:min-h-96"
    >
      <span class="text-muted">Please wait while we prepare the video...</span>
    </div>

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
            :label="caption.name || 'undefined'"
            kind="captions"
          />
        </template>
      </media-provider>
    </media-player>
  </PageSection>
</template>
