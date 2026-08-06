import type { Playlist } from '@/types'

export function usePlaylist() {
  const isPlaylistReplacement = (next: Playlist | null, current: Playlist | null): boolean =>
    next?.id !== current?.id || next?.asset !== current?.asset

  return {
    isPlaylistReplacement,
  }
}
