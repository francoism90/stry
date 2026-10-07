<script setup lang="ts">
import { show } from '@/actions/Modules/Web/Videos/Controllers/VideoController'
import type { ReelPreload } from '@/composables/reels'
import { useVideo } from '@/composables/video'
import type { VideoReel } from '@/types'
import { Link } from '@inertiajs/vue3'
import { useIntersectionObserver } from '@vueuse/core'
import { ref, watch } from 'vue'

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

const liked = ref(props.item.liked ?? false)
const saved = ref(props.item.saved ?? false)

const { toggleLike, toggleSave } = useVideo()

useIntersectionObserver(
  root,
  ([entry]) => {
    if (entry?.isIntersecting) {
      emit('visible')
    }
  },
  { threshold: 0.6 },
)

const play = (video: HTMLVideoElement): void => {
  // Browsers only autoplay muted video, and the attribute may not be set yet after hydration.
  video.muted = props.muted
  video.play().catch(() => {
    // Unmuted playback was blocked; continue muted.
    emit('blocked')
    video.muted = true
    video.play().catch(() => {})
  })
}

watch(
  () => [element.value, props.active, props.playing] as const,
  ([video, active, playing]) => {
    if (!video) return

    if (active && playing) {
      play(video)
      return
    }

    video.pause()

    if (!active) {
      video.currentTime = 0
    }
  },
  { flush: 'post' },
)

const onLike = (): void => {
  liked.value = !liked.value
  toggleLike(props.item, ['collections'])
}

const onSave = (): void => {
  saved.value = !saved.value
  toggleSave(props.item, ['collections'])
}
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
          @click="onLike"
        />

        <UButton
          icon="i-lucide-bookmark"
          :aria-label="saved ? 'Unsave' : 'Save'"
          :color="saved ? 'primary' : 'neutral'"
          variant="ghost"
          size="xl"
          :class="saved ? undefined : 'text-white'"
          @click="onSave"
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
