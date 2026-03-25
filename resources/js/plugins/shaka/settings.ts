import UserSettingsController from '@/actions/App/Api/Users/Controllers/UserSettingsController'
import type { PlayerSettings } from '@/types'
import { useHttp } from '@inertiajs/vue3'

export const defaults: PlayerSettings = {
  autoplay: true,
  muted: false,
  volume: 1,
  loop: false,
  captions: true,
  quality: 'auto',
  playback_speed: 1,
  audio_language: 'en',
  caption_language: 'en',
}

export function usePlayerSettings() {
  const http = useHttp({ player: null } as { player: Partial<PlayerSettings> | null })

  function updatePlayerSettings(settings: Partial<PlayerSettings>): void {
    http.player = settings
    http.patch(UserSettingsController.url())
  }

  return { updatePlayerSettings }
}
