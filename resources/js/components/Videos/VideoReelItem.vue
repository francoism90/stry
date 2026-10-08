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

const reelButton =
  'size-12 justify-center rounded-full border border-white/20 bg-black/45 text-white backdrop-blur-lg backdrop-saturate-160 hover:bg-black/60 hover:text-white'
</script>

<template>
  <section
    ref="root"
    class="relative isolate flex h-dvh w-full snap-start snap-always justify-center overflow-hidden bg-black"
  >
    <img
      v-if="loaded && item.thumb"
      :src="item.thumb"
      alt=""
      class="pointer-events-none absolute inset-0 -z-10 size-full scale-110 object-cover opacity-15 blur-3xl"
      aria-hidden="true"
    />

    <div class="relative h-full w-full max-w-[calc(100dvh*9/16)] overflow-hidden">
      <video
        v-if="loaded && item.reel_url"
        ref="element"
        :src="item.reel_url"
        :poster="item.thumb ?? undefined"
        :preload="preload"
        :muted="muted"
        class="size-full object-cover"
        loop
        playsinline
        @click="emit('toggleMute')"
      />

      <img
        v-else-if="item.thumb"
        :src="item.thumb"
        :alt="item.title"
        class="size-full object-cover opacity-60"
        loading="lazy"
      />

      <div
        class="pointer-events-none absolute inset-x-0 bottom-0 flex items-end justify-between gap-4 bg-linear-to-t from-black/70 to-transparent p-4 pt-16"
      >
        <Link
          :href="show.url(item.id)"
          class="pointer-events-auto line-clamp-2 font-semibold text-white capitalize drop-shadow-sm"
        >
          {{ item.title }}
        </Link>

        <div class="pointer-events-auto flex flex-col items-center gap-3">
          <UButton
            :icon="muted ? 'i-lucide-volume-x' : 'i-lucide-volume-2'"
            :aria-label="muted ? 'Unmute' : 'Mute'"
            color="neutral"
            variant="ghost"
            size="lg"
            :class="reelButton"
            @click="emit('toggleMute')"
          />

          <UButton
            icon="i-lucide-heart"
            :aria-label="liked ? 'Unlike' : 'Like'"
            :aria-pressed="liked"
            color="neutral"
            variant="ghost"
            size="lg"
            :class="[reelButton, { 'text-error hover:text-error': liked }]"
            :ui="{ leadingIcon: liked ? 'fill-current' : '' }"
            @click="like"
          />

          <UButton
            icon="i-lucide-bookmark"
            :aria-label="saved ? 'Unsave' : 'Save'"
            :aria-pressed="saved"
            color="neutral"
            variant="ghost"
            size="lg"
            :class="[reelButton, { 'text-primary hover:text-primary': saved }]"
            :ui="{ leadingIcon: saved ? 'fill-current' : '' }"
            @click="save"
          />

          <UButton
            :to="show.url(item.id)"
            icon="i-lucide-play"
            aria-label="Watch full video"
            color="neutral"
            variant="ghost"
            size="lg"
            :class="reelButton"
            :ui="{ leadingIcon: 'ms-0.5 fill-current' }"
          />
        </div>
      </div>
    </div>
  </section>
</template>
