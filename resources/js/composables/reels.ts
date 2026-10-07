import { useDocumentVisibility } from '@vueuse/core'
import { computed, ref } from 'vue'

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
