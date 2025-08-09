<script setup lang="ts">
import type { Media } from '@/types'
import type { PlayerSrc } from 'vidstack'
import 'vidstack/bundle'

interface Props {
  src?: PlayerSrc | null
  title?: string | null
  time?: number | null
  captions?: Media[] | null
}

defineProps<Props>()
</script>

<template>
  <media-player
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
