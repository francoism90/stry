<script setup lang="ts">
import 'vidstack/bundle'

interface Props {
  src?: string | undefined
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
    playsInline
  >
    <media-provider></media-provider>
    <media-video-layout></media-video-layout>
  </media-player>
</template>
