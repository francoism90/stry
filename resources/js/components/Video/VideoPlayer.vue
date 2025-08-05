<script setup lang="ts">
import type { PlayerSrc } from 'vidstack'
import 'vidstack/bundle'

interface Props {
  src: PlayerSrc
  title?: string | undefined
  time?: number | undefined
}

defineProps<Props>()
</script>

<template>
  <media-player
    .src="src"
    .title="title"
    .clipStartTime="time"
    .playsInline="true"
    .autoPlay="true"
    class="player max-h-64 rounded-xl sm:max-h-96 lg:max-h-2/5"
  >
    <media-provider></media-provider>
    <media-video-layout></media-video-layout>
  </media-player>
</template>
