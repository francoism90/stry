import shaka from 'shaka-player/dist/shaka-player.ui'

import { iconDataUrl } from './icons'

export class SeekRewind extends shaka.ui.Element {
  private button: HTMLButtonElement

  constructor(parent: HTMLElement, controls: shaka.ui.Controls) {
    super(parent, controls)

    this.button = document.createElement('button')
    this.button.type = 'button'
    this.button.title = 'Seek back 15 seconds'
    this.button.className = 'shaka-rewind hidden md:block'
    parent.appendChild(this.button)

    const icon = new shaka.ui.Icon(null, { url: iconDataUrl('rewind'), size: 24, path: null, viewBox: null })
    const svgEl = icon.getSvgElement()
    if (svgEl) {
      this.button.appendChild(svgEl)
    }

    const label = document.createElement('span')
    label.textContent = '15'
    label.className = 'sr-only'
    this.button.appendChild(label)

    this.button.addEventListener('click', () => {
      const video = this.controls?.getVideo()
      if (video) {
        video.currentTime = Math.max(0, video.currentTime - 15)
      }
    })
  }

  override release(): void {
    super.release()
  }
}
