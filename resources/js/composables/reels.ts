import { useVideo } from '@/composables/video'
import { loadShaka, resolveAssetUri } from '@/plugins/shaka'
import type { Playlist, VideoReel } from '@/types'
import { tryOnScopeDispose, useDocumentVisibility, useIntersectionObserver } from '@vueuse/core'
import type shaka from 'shaka-player/dist/shaka-player.ui'
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

  return {
    active,
    muted,
    playing,
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
 * Streams a short video without a reel into the reel's video element, as the video page does, since
 * its clip can't be played from a URL. Ready at once when there's no stream, for reels.
 */
export function useReelStream(
  element: MaybeRefOrGetter<HTMLVideoElement | undefined>,
  stream: MaybeRefOrGetter<Playlist | null>,
) {
  const ready = ref(toValue(stream) === null)
  let player: shaka.Player | undefined

  const destroy = (): void => {
    player?.destroy().catch(() => {})
    player = undefined
  }

  watch(
    () => [toValue(element), toValue(stream)] as const,
    async ([video, playlist]) => {
      destroy()
      ready.value = playlist === null

      if (!video || !playlist) {
        return
      }

      const shakaInstance = await loadShaka()
      const current = new shakaInstance.Player()
      player = current

      current.configure({ streaming: { preferNativeHls: true } })

      try {
        await current.attach(video)
        await current.load(resolveAssetUri(playlist, video) ?? playlist.asset)
      } catch {
        return
      }

      if (player === current) {
        ready.value = true
      }
    },
    { immediate: true },
  )

  tryOnScopeDispose(destroy)

  return { ready }
}

/**
 * Plays the reel while it's active and the page is visible, and rewinds it once it's swiped away.
 * When the browser blocks playback with sound, it plays muted and calls onBlocked. A play() that is
 * interrupted by swiping away is ignored, so it doesn't mute the feed.
 */
export function useReelPlayback(
  element: MaybeRefOrGetter<HTMLVideoElement | undefined>,
  options: {
    active: MaybeRefOrGetter<boolean>
    playing: MaybeRefOrGetter<boolean>
    muted: MaybeRefOrGetter<boolean>
    ready?: MaybeRefOrGetter<boolean>
    onBlocked: () => void
  },
) {
  const play = (video: HTMLVideoElement): void => {
    // Browsers only autoplay muted video, and the attribute may not be set yet after hydration.
    video.muted = toValue(options.muted)
    video.play().catch((error: unknown) => {
      if (!(error instanceof DOMException) || error.name !== 'NotAllowedError') {
        return
      }

      options.onBlocked()
      video.muted = true
      video.play().catch(() => {})
    })
  }

  watch(
    () =>
      [toValue(element), toValue(options.active), toValue(options.playing), toValue(options.ready ?? true)] as const,
    ([video, active, playing, ready]) => {
      if (!video || !ready) {
        return
      }

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
export function useReelActions(item: VideoReel) {
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
