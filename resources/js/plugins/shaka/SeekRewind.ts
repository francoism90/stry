import shaka from 'shaka-player/dist/shaka-player.ui'
import { iconDataUrl } from './icons'

export class SeekRewind extends shaka.ui.Element {
  private button: HTMLButtonElement

  constructor(parent: HTMLElement, controls: shaka.ui.Controls) {
    super(parent, controls)

    this.button = document.createElement('button')
    this.button.type = 'button'
    this.button.title = 'Seek back 15 seconds'
    this.button.className = 'shaka-seek-back-15 hidden md:inline-flex'

    const img = document.createElement('img')
    img.src = iconDataUrl('rewind')
    this.button.appendChild(img)

    const textLabel = document.createElement('span')
    textLabel.textContent = '15'
    this.button.appendChild(textLabel)

    parent.appendChild(this.button)

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
