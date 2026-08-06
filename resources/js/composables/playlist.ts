import type { Playlist } from '@/types'
import { router } from '@inertiajs/vue3'

export function usePlaylist() {
  let refreshTimer: ReturnType<typeof setTimeout> | undefined

  const isPlaylistReplacement = (next: Playlist | null, current: Playlist | null): boolean =>
    next?.id !== current?.id || next?.asset !== current?.asset

  const cancelAssetRefresh = () => clearTimeout(refreshTimer)

  // Refetch the playlist prop before its signed manifest url expires, so playback never hits a stale signature
  const scheduleAssetRefresh = (playlist: Playlist) => {
    cancelAssetRefresh()

    refreshTimer = setTimeout(() => router.reload({ only: ['playlist', 'progress'] }), playlist.asset_refresh_in * 1000)
  }

  return {
    isPlaylistReplacement,
    scheduleAssetRefresh,
    cancelAssetRefresh,
  }
}
