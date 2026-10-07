import { useVideo } from '@/composables/video'
import type { VideoReel } from '@/types'
import { useDocumentVisibility, useIntersectionObserver } from '@vueuse/core'
import { computed, ref, toValue, watch, type MaybeRefOrGetter } from 'vue'

export type ReelPreload = 'auto' | 'none'

/**
 * Reels kept loaded before and after the active one, so swiping back or ahead plays at once.
 */
export const REELS_BEHIND = 1
export const REELS_AHEAD = 2

export function isReelLoaded(index: number, active: number): boolean {
  return index >= active - REELS_BEHIND && index <= active + REELS_AHEAD
}

export function reelPreload(index: number, active: number): ReelPreload {
  return index >= active && index <= active + REELS_AHEAD ? 'auto' : 'none'
}

export function useReelFeed() {
  const active = ref(0)
  const muted = ref(true)
  const visibility = useDocumentVisibility()

  const playing = computed(() => visibility.value === 'visible')

  const activate = (index: number): void => {
    active.value = index
  }

  const toggleMute = (): void => {
    muted.value = !muted.value
  }

  return {
    active,
    muted,
    playing,
    activate,
    toggleMute,
    isLoaded: (index: number) => isReelLoaded(index, active.value),
    preload: (index: number) => reelPreload(index, active.value),
  }
}

/**
 * Calls back when most of the reel is in view, so the feed can make it the active one.
 */
export function useReelVisibility(root: MaybeRefOrGetter<HTMLElement | undefined>, onVisible: () => void) {
  useIntersectionObserver(
    root,
    ([entry]) => {
      if (entry?.isIntersecting) {
        onVisible()
      }
    },
    { threshold: 0.6 },
  )
}

/**
 * Plays the reel while it's active and the page is visible, and rewinds it once it's swiped away.
 * When the browser blocks playback with sound, it plays muted and calls onBlocked.
 */
export function useReelPlayback(
  element: MaybeRefOrGetter<HTMLVideoElement | undefined>,
  options: {
    active: MaybeRefOrGetter<boolean>
    playing: MaybeRefOrGetter<boolean>
    muted: MaybeRefOrGetter<boolean>
    onBlocked: () => void
  },
) {
  const play = (video: HTMLVideoElement): void => {
    // Browsers only autoplay muted video, and the attribute may not be set yet after hydration.
    video.muted = toValue(options.muted)
    video.play().catch(() => {
      options.onBlocked()
      video.muted = true
      video.play().catch(() => {})
    })
  }

  watch(
    () => [toValue(element), toValue(options.active), toValue(options.playing)] as const,
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
}

/**
 * Likes and saves a reel. The buttons update at once, and only the sidebar's collections reload,
 * so the feed keeps its order.
 */
export function useReelGroups(item: VideoReel) {
  const { toggleLike, toggleSave } = useVideo()

  const liked = ref(item.liked ?? false)
  const saved = ref(item.saved ?? false)

  const like = (): void => {
    liked.value = !liked.value
    toggleLike(item, ['collections'])
  }

  const save = (): void => {
    saved.value = !saved.value
    toggleSave(item, ['collections'])
  }

  return { liked, saved, like, save }
}
