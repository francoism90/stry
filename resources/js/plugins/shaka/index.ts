import type shaka from 'shaka-player/dist/shaka-player.ui'

let _shaka: typeof shaka | undefined

export async function loadShaka(): Promise<typeof shaka> {
  if (!_shaka) {
    const [mod, { SeekBack }, { SeekForward }] = await Promise.all([
      import('shaka-player/dist/shaka-player.ui'),
      import('./SeekBack'),
      import('./SeekForward'),
    ])

    _shaka = mod.default as unknown as typeof shaka

    _shaka.polyfill.installAll()

    _shaka.ui.Controls.registerElement('seek_back_15', {
      create: (parent: HTMLElement, controls: shaka.ui.Controls) => new SeekBack(parent, controls),
    })

    _shaka.ui.Controls.registerElement('seek_forward_15', {
      create: (parent: HTMLElement, controls: shaka.ui.Controls) => new SeekForward(parent, controls),
    })
  }

  return _shaka
}

export function getShaka(): typeof shaka | undefined {
  return _shaka
}

export function configureOverlay(overlay: shaka.ui.Overlay): void {
  overlay.configure({
    doubleClickForFullscreen: false,
    singleClickForPlayAndPause: false,
    seekOnTaps: true,
    tapSeekDistance: 15,
    controlPanelElements: [
      'play_pause',
      'seek_back_15',
      'seek_forward_15',
      'time_and_duration',
      'spacer',
      'mute',
      'volume',
      'cast',
      'overflow_menu',
      'fullscreen',
    ],
  })
}
