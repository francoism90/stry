<script setup lang="ts">
import { useTemplateRef } from 'vue'

const shakaUi = useTemplateRef('shaka-ui')
const shakaVideo = useTemplateRef('shaka-video')

defineExpose({
  shakaUi,
  shakaVideo,
})
</script>

<template>
  <div
    data-shaka-player-container
    ref="shaka-ui"
  >
    <video
      data-shaka-player
      ref="shaka-video"
      class="aspect-video min-h-52 w-full rounded-2xl bg-black"
      autoplay
      controls
      playsinline
    />
  </div>
</template>
