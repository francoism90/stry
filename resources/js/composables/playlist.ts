import type { Playlist } from '@/types'

/** Whether `next` is a different playlist row, or the same row re-signed with a new asset url. */
export const isPlaylistReplacement = (next: Playlist | null, current: Playlist | null): boolean =>
  next?.id !== current?.id || next?.asset !== current?.asset
