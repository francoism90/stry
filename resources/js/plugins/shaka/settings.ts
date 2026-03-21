import type { PlayerSettings } from '@/types'

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

export function playerSetting<K extends keyof PlayerSettings>(
  settings: PlayerSettings | null,
  key: K,
): PlayerSettings[K] {
  return settings?.[key] ?? defaults[key]
}
