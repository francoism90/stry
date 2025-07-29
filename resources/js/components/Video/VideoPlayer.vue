<script setup lang="ts">
import 'vidstack/bundle'

interface Props {
  src?: string
  title?: string
}

defineProps<Props>()
</script>

<template>
  <media-player
    .src="src"
    autoPlay
    playsInline
  >
    <media-provider></media-provider>
    <media-video-layout></media-video-layout>
  </media-player>
</template>
