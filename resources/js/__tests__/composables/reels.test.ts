import { isReelLoaded, reelPreload, useReelPlayback, useReelStream } from '@/composables/reels'
import type { Playlist } from '@/types'
import { describe, expect, it, vi } from 'vite-plus/test'
import { effectScope, nextTick, ref } from 'vue'

const shaka = vi.hoisted(() => {
  const Player = vi.fn<(this: Record<string, unknown>) => void>(function (this: Record<string, unknown>) {
    this.configure = vi.fn<() => void>()
    this.attach = vi.fn<() => Promise<void>>(() => Promise.resolve())
    this.load = vi.fn<() => Promise<void>>(() => Promise.resolve())
    this.destroy = vi.fn<() => Promise<void>>(() => Promise.resolve())
  })

  let resolveLoad: (instance: unknown) => void = () => {}

  return {
    Player,
    loadShaka: vi.fn<() => Promise<unknown>>(() => new Promise((resolve) => (resolveLoad = resolve))),
    finishLoading: () => resolveLoad({ Player }),
  }
})

vi.mock('@/plugins/shaka', () => ({
  loadShaka: shaka.loadShaka,
  resolveAssetUri: (playlist: Playlist) => playlist.asset,
}))

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

  it('waits for the stream to be ready before playing', async () => {
    const video = {
      muted: false,
      play: vi.fn<() => Promise<void>>().mockResolvedValue(),
      pause: vi.fn<() => void>(),
    }
    const element = ref<HTMLVideoElement>(video as unknown as HTMLVideoElement)
    const ready = ref(false)

    useReelPlayback(element, { active: true, playing: true, muted: true, ready, onBlocked: () => {} })
    await nextTick()

    expect(video.play).not.toHaveBeenCalled()

    ready.value = true
    await nextTick()

    expect(video.play).toHaveBeenCalledOnce()
  })
})

describe('useReelStream', () => {
  it('does not create a player when the reel is swiped away while Shaka loads', async () => {
    const scope = effectScope()
    const video = {} as HTMLVideoElement

    scope.run(() => useReelStream(video, { id: '1', asset: 'https://example.test/reel.m3u8' } as Playlist))
    await nextTick()
    expect(shaka.loadShaka).toHaveBeenCalledOnce()

    scope.stop()
    shaka.finishLoading()
    await Promise.resolve()
    await Promise.resolve()

    expect(shaka.Player).not.toHaveBeenCalled()
  })
})
