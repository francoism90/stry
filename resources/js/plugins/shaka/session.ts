import PlaylistSessionController from '@/actions/App/Api/Playlists/Controllers/PlaylistSessionController'
import type { Playlist } from '@/types'
import { useHttp } from '@inertiajs/vue3'

export function usePlaylistSession() {
  const http = useHttp({ time: 0 })

  function updatePlaylistSession(playlist: Playlist, time: number): void {
    http.time = time
    http.post(PlaylistSessionController.url(playlist.id))
  }

  return { updatePlaylistSession }
}
