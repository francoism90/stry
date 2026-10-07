<script setup lang="ts">
import { show } from '@/actions/Modules/Web/Videos/Controllers/VideoController'
import { useReelGroups, useReelPlayback, useReelVisibility, type ReelPreload } from '@/composables/reels'
import type { VideoReel } from '@/types'
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps<{
  item: VideoReel
  active: boolean
  loaded: boolean
  preload: ReelPreload
  muted: boolean
  playing: boolean
}>()

const emit = defineEmits<{
  visible: []
  toggleMute: []
  blocked: []
}>()

const root = ref<HTMLElement>()
const element = ref<HTMLVideoElement>()

useReelVisibility(root, () => emit('visible'))

useReelPlayback(element, {
  active: () => props.active,
  playing: () => props.playing,
  muted: () => props.muted,
  onBlocked: () => emit('blocked'),
})

const { liked, saved, like, save } = useReelGroups(props.item)
</script>

<template>
  <section
    ref="root"
    class="relative flex h-dvh w-full snap-start snap-always items-center justify-center bg-black"
  >
    <video
      v-if="loaded && item.reel_url"
      ref="element"
      :src="item.reel_url"
      :poster="item.thumb ?? undefined"
      :preload="preload"
      :muted="muted"
      class="h-full w-full object-contain"
      loop
      playsinline
      @click="emit('toggleMute')"
    />

    <img
      v-else-if="item.thumb"
      :src="item.thumb"
      :alt="item.title"
      class="h-full w-full object-cover opacity-60"
      loading="lazy"
    />

    <div
      class="pointer-events-none absolute inset-x-0 bottom-0 flex items-end justify-between gap-4 bg-linear-to-t from-black/70 to-transparent p-4 pt-16"
    >
      <Link
        :href="show.url(item.id)"
        class="pointer-events-auto line-clamp-2 font-semibold text-white"
      >
        {{ item.title }}
      </Link>

      <div class="pointer-events-auto flex flex-col items-center gap-2">
        <UButton
          :icon="muted ? 'i-lucide-volume-x' : 'i-lucide-volume-2'"
          :aria-label="muted ? 'Unmute' : 'Mute'"
          color="neutral"
          variant="ghost"
          size="xl"
          class="text-white"
          @click="emit('toggleMute')"
        />

        <UButton
          icon="i-lucide-heart"
          :aria-label="liked ? 'Unlike' : 'Like'"
          :color="liked ? 'error' : 'neutral'"
          variant="ghost"
          size="xl"
          :class="liked ? undefined : 'text-white'"
          @click="like"
        />

        <UButton
          icon="i-lucide-bookmark"
          :aria-label="saved ? 'Unsave' : 'Save'"
          :color="saved ? 'primary' : 'neutral'"
          variant="ghost"
          size="xl"
          :class="saved ? undefined : 'text-white'"
          @click="save"
        />

        <UButton
          :to="show.url(item.id)"
          icon="i-lucide-play"
          aria-label="Watch full video"
          color="neutral"
          variant="ghost"
          size="xl"
          class="text-white"
        />
      </div>
    </div>
  </section>
</template>
