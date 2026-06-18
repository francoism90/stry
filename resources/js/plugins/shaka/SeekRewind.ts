import shaka from 'shaka-player/dist/shaka-player.ui'
import { iconDataUrl } from './icons'

export class SeekRewind extends shaka.ui.Element {
  private button: HTMLButtonElement

  constructor(parent: HTMLElement, controls: shaka.ui.Controls) {
    super(parent, controls)

    this.button = document.createElement('button')
    this.button.type = 'button'
    this.button.title = 'Seek back 15 seconds'
    this.button.className = 'shaka-seek-rewind hidden md:block'
    this.button.style.width = '32px'
    this.button.style.height = '32px'
    this.button.style.backgroundColor = 'currentColor'

    const maskUrl = `url("${iconDataUrl('rewind')}")`
    this.button.style.maskImage = maskUrl
    this.button.style.webkitMaskImage = maskUrl
    this.button.style.maskRepeat = 'no-repeat'
    this.button.style.webkitMaskRepeat = 'no-repeat'
    this.button.style.maskPosition = 'center'
    this.button.style.webkitMaskPosition = 'center'
    this.button.style.maskSize = '24px'
    this.button.style.webkitMaskSize = '24px'

    parent.appendChild(this.button)

    const label = document.createElement('span')
    label.textContent = '15'
    label.className = 'sr-only'
    this.button.appendChild(label)

    this.eventManager?.listen(this.button, 'click', () => {
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
