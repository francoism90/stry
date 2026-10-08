import { isReelLoaded, reelPreload, useReelPlayback } from '@/composables/reels'
import { describe, expect, it, vi } from 'vite-plus/test'
import { nextTick, ref } from 'vue'

describe('isReelLoaded', () => {
  it('keeps the previous reel and the next two loaded around the active one', () => {
    const loaded = [0, 1, 2, 3, 4, 5, 6].filter((index) => isReelLoaded(index, 3))

    expect(loaded).toEqual([2, 3, 4, 5])
  })
})

describe('reelPreload', () => {
  it('preloads the active reel and the next two only', () => {
    const preloads = [2, 3, 4, 5, 6].map((index) => reelPreload(index, 3))

    expect(preloads).toEqual(['none', 'auto', 'auto', 'auto', 'none'])
  })
})

describe('useReelPlayback', () => {
  const playWith = async (error: DOMException) => {
    const onBlocked = vi.fn<() => void>()
    const video = {
      muted: false,
      play: vi.fn<() => Promise<void>>().mockRejectedValue(error),
      pause: vi.fn<() => void>(),
    }
    const element = ref<HTMLVideoElement>()

    useReelPlayback(element, { active: true, playing: true, muted: false, onBlocked })

    element.value = video as unknown as HTMLVideoElement
    await nextTick()
    await Promise.resolve()

    return { onBlocked, video }
  }

  it('plays muted when the browser blocks playback with sound', async () => {
    const { onBlocked, video } = await playWith(new DOMException('Blocked', 'NotAllowedError'))

    expect(onBlocked).toHaveBeenCalledOnce()
    expect(video.muted).toBe(true)
  })

  it('does not mute the feed when the reel is swiped away before it plays', async () => {
    const { onBlocked, video } = await playWith(new DOMException('Interrupted', 'AbortError'))

    expect(onBlocked).not.toHaveBeenCalled()
    expect(video.muted).toBe(false)
  })
})
