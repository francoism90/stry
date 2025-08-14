<script setup lang="ts">
import PlaylistSessionController from '@/actions/App/Api/Playlists/Controllers/PlaylistSessionController'
import type { Media, Playlist } from '@/types'
import { useThrottleFn } from '@vueuse/core'
import type { MediaPlayer } from 'vidstack'
import 'vidstack/bundle'
import { computed, onMounted, ref } from 'vue'

interface Props {
  playlist: Playlist | null
  captions?: Media[] | null
  title?: string | null
  time?: number | null
}

const props = defineProps<Props>()
const player = ref<MediaPlayer | null>(null)

const src = computed(() => (props.playlist?.valid ? props.playlist.asset : ''))
const time = computed(() => props.time ?? 0)

onMounted(() => {
  player.value?.subscribe(({ currentTime }) => {
    if (Number.isNaN(currentTime) || !props.playlist?.valid) {
      return
    }

    useThrottleFn(() => PlaylistSessionController.url({ playlist: props.playlist?.id || '' }), 1000)

    // console.log('Paused:', currentTime)
  })
})
</script>

<template>
  <media-player
    ref="player"
    .src="src || undefined"
    .title="title || undefined"
    .clipStartTime="time || undefined"
    .playsInline="true"
    .autoPlay="true"
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
