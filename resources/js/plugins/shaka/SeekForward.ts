import shaka from 'shaka-player/dist/shaka-player.ui'
import { iconDataUrl } from './icons'

export class SeekForward extends shaka.ui.Element {
  private button: HTMLButtonElement

  constructor(parent: HTMLElement, controls: shaka.ui.Controls) {
    super(parent, controls)

    this.button = document.createElement('button')
    this.button.type = 'button'
    this.button.title = 'Seek forward 30 seconds'
    this.button.className = 'shaka-seek-forward hidden md:block'
    this.button.style.width = '32px'
    this.button.style.height = '32px'
    this.button.style.backgroundColor = 'currentColor'

    const maskUrl = `url("${iconDataUrl('fast-forward')}")`
    this.button.style.maskImage = maskUrl
    this.button.style.webkitMaskImage = maskUrl
    this.button.style.maskRepeat = 'no-repeat'
    this.button.style.webkitMaskRepeat = 'no-repeat'
    this.button.style.maskPosition = 'center'
    this.button.style.webkitMaskPosition = 'center'
    this.button.style.maskSize = '24px' // Standard player button density
    this.button.style.webkitMaskSize = '24px'

    parent.appendChild(this.button)

    const label = document.createElement('span')
    label.textContent = '30'
    label.className = 'sr-only'
    this.button.appendChild(label)

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
