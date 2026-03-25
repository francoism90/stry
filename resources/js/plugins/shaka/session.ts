import PlaylistSessionController from '@/actions/App/Api/Playlists/Controllers/PlaylistSessionController'
import type { Playlist } from '@/types'
import { http } from '@inertiajs/vue3'

export function updatePlaylistSession(playlist: Playlist, time: number): void {
  http.getClient().request({ method: 'POST', url: PlaylistSessionController.url(playlist.id), data: { time } })
}
