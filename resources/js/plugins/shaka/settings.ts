import UserSettingsController from '@/actions/App/Api/Users/Controllers/UserSettingsController'
import type { PlayerSettings } from '@/types'
import { http } from '@inertiajs/vue3'

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

export function updatePlayerSettings(settings: Partial<PlayerSettings>): void {
  http.getClient().request({ method: 'PATCH', url: UserSettingsController.url(), data: { player: settings } })
}
