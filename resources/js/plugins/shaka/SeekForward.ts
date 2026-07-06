import shaka from 'shaka-player/dist/shaka-player.ui'
import { iconDataUrl } from './icons'

export class SeekForward extends shaka.ui.Element {
  private button: HTMLButtonElement

  constructor(parent: HTMLElement, controls: shaka.ui.Controls) {
    super(parent, controls)

    this.button = document.createElement('button')
    this.button.type = 'button'
    this.button.ariaLabel = 'Seek forward 30 seconds'
    this.button.className = 'shaka-seek-button shaka-tooltip shaka-no-propagation'
    parent.appendChild(this.button)

    const icon = new shaka.ui.Icon(null, { url: iconDataUrl('fast-forward'), size: 24, path: null, viewBox: null })
    const svgEl = icon.getSvgElement()
    if (svgEl) {
      this.button.appendChild(svgEl)
    }

    this.eventManager?.listen(this.button, 'click', () => {
      const video = this.controls?.getVideo()
      if (video) {
        video.currentTime = Math.min(video.duration || Infinity, video.currentTime + 30)
      }
    })
  }

  override release(): void {
    super.release()
  }
}
