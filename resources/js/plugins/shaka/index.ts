import type shaka from 'shaka-player/dist/shaka-player.ui'
import { registerLucideIcons } from './icons'

let instance: typeof shaka | undefined

export async function loadShaka(): Promise<typeof shaka> {
  if (!instance) {
    const [mod, seekRewindMod, seekForwardMod] = await Promise.all([
      import('shaka-player/dist/shaka-player.ui'),
      import('shaka-player/dist/controls.css'),
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
  }

  return instance
}

export function getShaka(): typeof shaka | undefined {
  return instance
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
      'cast',
      'overflow_menu',
      'fullscreen',
    ],
  })
}
