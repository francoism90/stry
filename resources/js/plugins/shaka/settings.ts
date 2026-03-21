import UserSettingsController from '@/actions/App/Api/Users/Controllers/UserSettingsController'
import type { PlayerSettings } from '@/types'
import { http } from '@/utils/http'

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
  http.patch(UserSettingsController.url(), { player: settings })
}
