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

    // Custom SVG data URL injected cleanly via CSS mask-image properties
    this.button.style.maskImage = `url("${iconDataUrl('rewind')}")`
    this.button.style.webkitMaskImage = `url("${iconDataUrl('rewind')}")`
    this.button.style.maskRepeat = 'no-repeat'
    this.button.style.maskPosition = 'center'

    parent.appendChild(this.button)

    const label = document.createElement('span')
    label.textContent = '15'
    label.className = 'sr-only'
    this.button.appendChild(label)

    // Register event binding via native Shaka EventManager
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
