import type { Playlist } from '@/types'
import type shaka from 'shaka-player/dist/shaka-player.ui'
import { registerLucideIcons } from './icons'

let instance: typeof shaka | undefined

export async function loadShaka(): Promise<typeof shaka> {
  if (!instance) {
    const [mod, seekRewindMod, seekForwardMod] = await Promise.all([
      import('shaka-player/dist/shaka-player.ui'),
      import('./SeekRewind') as Promise<{
        SeekRewind: new (parent: HTMLElement, controls: shaka.ui.Controls) => shaka.ui.Element
      }>,
      import('./SeekForward') as Promise<{
        SeekForward: new (parent: HTMLElement, controls: shaka.ui.Controls) => shaka.ui.Element
      }>,
    ])

    instance = mod.default as unknown as typeof shaka

    instance.polyfill.installAll()

    registerLucideIcons(instance)

    instance.ui.Controls.registerElement('seek_rewind', {
      create: (parent: HTMLElement, controls: shaka.ui.Controls) => new seekRewindMod.SeekRewind(parent, controls),
    })

    instance.ui.Controls.registerElement('seek_forward', {
      create: (parent: HTMLElement, controls: shaka.ui.Controls) => new seekForwardMod.SeekForward(parent, controls),
    })

    guardWindowKeyboardShortcuts()
  }

  return instance
}

// Registered before the UI overlay so this runs first: stops Shaka's window-level keydown
// shortcuts (enableKeyboardPlaybackControlsInWindow) from firing while the user is typing elsewhere.
function guardWindowKeyboardShortcuts(): void {
  window.addEventListener('keydown', (event) => {
    const target = event.target as HTMLElement | null
    const isEditable = target?.tagName === 'INPUT' || target?.tagName === 'TEXTAREA' || target?.isContentEditable

    if (isEditable) {
      event.stopImmediatePropagation()
    }
  })
}

export function getShaka(): typeof shaka | undefined {
  return instance
}

// Throws if Shaka hasn't been loaded yet, since the error classes only exist on the loaded instance.
export function createError(
  code: keyof typeof shaka.util.Error.Code | null = null,
  category: keyof typeof shaka.util.Error.Category | null = null,
  severity: keyof typeof shaka.util.Error.Severity | null = null,
): shaka.util.Error {
  if (!instance) {
    throw new Error('Shaka Player has not been loaded yet.')
  }

  return new instance.util.Error(
    instance.util.Error.Severity[severity ?? 'CRITICAL'],
    (category !== null ? instance.util.Error.Category[category] : null) as shaka.util.Error.Category,
    (code !== null ? instance.util.Error.Code[code] : null) as shaka.util.Error.Code,
  )
}

// shaka.util.Error is only critical at CRITICAL severity; any other thrown Error always is.
export function isCriticalError(error: shaka.util.Error | Error): boolean {
  const ShakaError = instance?.util.Error

  if (ShakaError && error instanceof ShakaError) {
    return error.severity === ShakaError.Severity.CRITICAL
  }

  return error instanceof Error
}

// Chrome reports a false positive for canPlayType('application/vnd.apple.mpegurl') without
// actually having a native HLS demuxer, so this also requires the Apple vendor string.
export function supportsNativeHls(mediaElement: HTMLMediaElement): boolean {
  return navigator.vendor.includes('Apple') && mediaElement.canPlayType('application/vnd.apple.mpegurl') !== ''
}

// Prefers the HLS master playlist on platforms with native HLS support (paired with
// `preferNativeHls` in the player config), DASH everywhere else.
export function resolveAssetUri(playlist: Playlist, mediaElement: HTMLMediaElement | null): string | null {
  if (mediaElement && playlist.asset_hls && supportsNativeHls(mediaElement)) {
    return playlist.asset_hls
  }

  return playlist.asset
}

export function configureOverlay(overlay: shaka.ui.Overlay): void {
  overlay.configure({
    doubleClickForFullscreen: false,
    singleClickForPlayAndPause: false,
    enableFullscreenOnRotation: true,
    forceLandscapeOnFullscreen: true,
    enableKeyboardPlaybackControlsInWindow: true,
    keyboardSeekDistance: 10,
    keyboardLargeSeekDistance: 30,
    seekOnTaps: true,
    bigButtons: [],
    seekBarColors: {
      chapters: 'rgba(255, 255, 255, 0.7)',
    },
    controlPanelElements: [
      'play_pause',
      'mute',
      'skip_previous',
      'skip_next',
      'seek_rewind',
      'seek_forward',
      'time_and_duration',
      'spacer',
      'captions',
      'chapter',
      'cast',
      'overflow_menu',
      'fullscreen',
    ],
  })
}
