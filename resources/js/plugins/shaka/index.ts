import shaka from 'shaka-player/dist/shaka-player.ui'
import { SeekBack } from './SeekBack'
import { SeekForward } from './SeekForward'

shaka.ui.Controls.registerElement('seek_back_15', {
  create: (parent: HTMLElement, controls: shaka.ui.Controls) => new SeekBack(parent, controls),
})

shaka.ui.Controls.registerElement('seek_forward_15', {
  create: (parent: HTMLElement, controls: shaka.ui.Controls) => new SeekForward(parent, controls),
})

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
      'skip_previous',
      'skip_next',
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

export { useShakaStorage } from './storage'
export { SeekBack, SeekForward }
