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

    // This mirrors how Shaka natively applies icons to remain styleable by currentColor
    this.button.style.maskImage = `url("${iconDataUrl('fast-forward')}")`
    this.button.style.webkitMaskImage = `url("${iconDataUrl('fast-forward')}")`
    this.button.style.maskRepeat = 'no-repeat'
    this.button.style.maskPosition = 'center'

    parent.appendChild(this.button)

    const label = document.createElement('span')
    label.textContent = '30'
    label.className = 'sr-only'
    this.button.appendChild(label)

    // ✅ Use native Shaka EventManager
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
