import iconSet from '@iconify-json/lucide/icons.json'

export const ShakaIcon = {
  Airplay: 'AIRPLAY',
  Back: 'BACK',
  Cast: 'CAST',
  Chapter: 'CHAPTER',
  Checkmark: 'CHECKMARK',
  Close: 'CLOSE',
  ClosedCaptions: 'CLOSED_CAPTIONS',
  ClosedCaptionsOff: 'CLOSED_CAPTIONS_OFF',
  ClosedCaptionsPosition: 'CLOSED_CAPTIONS_POSITION',
  ClosedCaptionsSize: 'CLOSED_CAPTIONS_SIZE',
  Copy: 'COPY',
  Download: 'DOWNLOAD',
  ExitCast: 'EXIT_CAST',
  ExitFullscreen: 'EXIT_FULLSCREEN',
  ExitPip: 'EXIT_PIP',
  FastForward: 'FAST_FORWARD',
  Fullscreen: 'FULLSCREEN',
  Language: 'LANGUAGE',
  Loop: 'LOOP',
  Mute: 'MUTE',
  OpenOverflow: 'OPEN_OVERFLOW',
  Pause: 'PAUSE',
  Pip: 'PIP',
  Play: 'PLAY',
  PlaybackRate: 'PLAYBACK_RATE',
  RecenterVr: 'RECENTER_VR',
  Replay: 'REPLAY',
  Resolution: 'RESOLUTION',
  Rewind: 'REWIND',
  SkipNext: 'SKIP_NEXT',
  SkipPrevious: 'SKIP_PREVIOUS',
  StatisticsOff: 'STATISTICS_OFF',
  StatisticsOn: 'STATISTICS_ON',
  ToggleStereoscopic: 'TOGGLE_STEREOSCOPIC',
  Unloop: 'UNLOOP',
  Unmute: 'UNMUTE',
  VideoType: 'VIDEO_TYPE',
} as const

export type ShakaIconName = (typeof ShakaIcon)[keyof typeof ShakaIcon]

const lucideMap: Record<ShakaIconName, string> = {
  AIRPLAY: 'airplay',
  BACK: 'arrow-left',
  CAST: 'cast',
  CHAPTER: 'list',
  CHECKMARK: 'check',
  CLOSE: 'x',
  CLOSED_CAPTIONS: 'captions',
  CLOSED_CAPTIONS_OFF: 'captions-off',
  CLOSED_CAPTIONS_POSITION: 'move',
  CLOSED_CAPTIONS_SIZE: 'type',
  COPY: 'copy',
  DOWNLOAD: 'download',
  EXIT_CAST: 'cast',
  EXIT_FULLSCREEN: 'minimize',
  EXIT_PIP: 'picture-in-picture-2',
  FAST_FORWARD: 'fast-forward',
  FULLSCREEN: 'maximize',
  LANGUAGE: 'languages',
  LOOP: 'repeat',
  MUTE: 'volume-2',
  OPEN_OVERFLOW: 'ellipsis-vertical',
  PAUSE: 'pause',
  PIP: 'picture-in-picture',
  PLAY: 'play',
  PLAYBACK_RATE: 'gauge',
  RECENTER_VR: 'crosshair',
  REPLAY: 'rotate-ccw',
  RESOLUTION: 'monitor',
  REWIND: 'rewind',
  SKIP_NEXT: 'skip-forward',
  SKIP_PREVIOUS: 'skip-back',
  STATISTICS_OFF: 'bar-chart-3',
  STATISTICS_ON: 'bar-chart-4',
  TOGGLE_STEREOSCOPIC: 'glasses',
  UNLOOP: 'repeat-1',
  UNMUTE: 'volume-x',
  VIDEO_TYPE: 'film',
}

type IconSetIcons = typeof iconSet.icons
type LucideIconName = keyof IconSetIcons

export function iconDataUrl(iconName: LucideIconName): string {
  const icon = (iconSet.icons as Record<string, { body: string; width?: number; height?: number }>)[iconName]

  if (!icon) return 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'

  const width = icon.width ?? iconSet.width
  const height = icon.height ?? iconSet.height
  const body = icon.body

  const svg = `<svg xmlns="http://w3.org" viewBox="0 0 ${width} ${height}" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${body}</svg>`

  return `data:image/svg+xml;charset=utf-8,${encodeURIComponent(svg)}`
}

export function registerLucideIcons(shaka: typeof import('shaka-player/dist/shaka-player.ui').default): void {
  for (const [shakaName, lucideName] of Object.entries(lucideMap) as [ShakaIconName, LucideIconName][]) {
    shaka.ui.IconRegistry.register(shakaName, {
      url: iconDataUrl(lucideName),
      size: 24,
      path: null,
      viewBox: null,
    })
  }
}
