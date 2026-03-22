import PlaylistSessionController from '@/actions/App/Api/Playlists/Controllers/PlaylistSessionController'
import type { Playlist } from '@/types'
import { http } from '@/utils/http'

export function updatePlaylistSession(playlist: Playlist, time: number): void {
  http.post(PlaylistSessionController.url(playlist.id), { time })
}
