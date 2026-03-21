import type { PlayerSettings } from '@/types'

export const defaults: PlayerSettings = {
  autoplay: true,
  muted: false,
  loop: false,
  captions: true,
  quality: 'auto',
  playback_speed: 1,
  audio_languages: 'en',
  caption_languages: 'en',
}

export function playerSetting<K extends keyof PlayerSettings>(
  settings: PlayerSettings | null,
  key: K,
): PlayerSettings[K] {
  return settings?.[key] ?? defaults[key]
}
