import { usePlaylist } from '@/composables/playlist'
import type { Playlist } from '@/types'
import { router } from '@inertiajs/vue3'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

vi.mock('@inertiajs/vue3', () => ({
  router: { reload: vi.fn() },
}))

const playlist = (id: string, asset: string, assetRefreshIn = 0): Playlist =>
  ({ id, asset, asset_refresh_in: assetRefreshIn }) as Playlist

describe('isPlaylistReplacement', () => {
  const { isPlaylistReplacement } = usePlaylist()

  it('is not a replacement when id and asset are unchanged', () => {
    const current = playlist('1', 'https://example.test/a?expires=1')

    expect(isPlaylistReplacement(playlist('1', 'https://example.test/a?expires=1'), current)).toBe(false)
  })

  it('is a replacement when the id changes', () => {
    const current = playlist('1', 'https://example.test/a?expires=1')

    expect(isPlaylistReplacement(playlist('2', 'https://example.test/a?expires=1'), current)).toBe(true)
  })

  it('is a replacement when only the asset (a re-signed url) changes', () => {
    const current = playlist('1', 'https://example.test/a?expires=1')

    expect(isPlaylistReplacement(playlist('1', 'https://example.test/a?expires=2'), current)).toBe(true)
  })

  it('is not a replacement when there is nothing loaded yet', () => {
    expect(isPlaylistReplacement(null, null)).toBe(false)
  })
})

describe('scheduleAssetRefresh', () => {
  beforeEach(() => vi.useFakeTimers())
  afterEach(() => {
    vi.useRealTimers()
    vi.mocked(router.reload).mockClear()
  })

  it('reloads the playlist prop once the refresh window elapses', () => {
    const { scheduleAssetRefresh } = usePlaylist()

    scheduleAssetRefresh(playlist('1', 'https://example.test/a', 300))
    vi.advanceTimersByTime(299_999)
    expect(router.reload).not.toHaveBeenCalled()

    vi.advanceTimersByTime(1)
    expect(router.reload).toHaveBeenCalledWith({ only: ['playlist', 'progress'] })
  })

  it('reschedules from the latest call instead of stacking timers', () => {
    const { scheduleAssetRefresh } = usePlaylist()

    scheduleAssetRefresh(playlist('1', 'https://example.test/a', 300))
    vi.advanceTimersByTime(200_000)
    scheduleAssetRefresh(playlist('1', 'https://example.test/a', 300))
    vi.advanceTimersByTime(299_999)

    expect(router.reload).not.toHaveBeenCalled()
  })

  it('does not reload once cancelled', () => {
    const { scheduleAssetRefresh, cancelAssetRefresh } = usePlaylist()

    scheduleAssetRefresh(playlist('1', 'https://example.test/a', 300))
    cancelAssetRefresh()
    vi.advanceTimersByTime(300_000)

    expect(router.reload).not.toHaveBeenCalled()
  })
})
