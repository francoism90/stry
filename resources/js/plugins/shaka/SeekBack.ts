import shaka from 'shaka-player/dist/shaka-player.ui'

const SEEK_BACK_PATH =
  'M480-80q-75 0-140.5-28.5t-114-77q-48.5-48.5-77-114T120-440q0-17 11.5-28.5T160-480q17 0 28.5 11.5T200-440q0 117 81.5 198.5T480-160q117 0 198.5-81.5T760-440q0-117-81.5-198.5T480-720h-6l34 34q12 12 11.5 28T508-630q-12 12-28.5 12.5T451-629L348-732q-12-12-12-28t12-28l103-103q12-12 28.5-11.5T508-890q11 12 11.5 28T508-834l-34 34h6q75 0 140.5 28.5t114 77q48.5 48.5 77 114T840-440q0 75-28.5 140.5t-77 114q-48.5 48.5-114 77T480-80Z'

export class SeekBack extends shaka.ui.Element {
  private button: HTMLButtonElement

  constructor(parent: HTMLElement, controls: shaka.ui.Controls) {
    super(parent, controls)

    this.button = document.createElement('button')
    this.button.type = 'button'
    this.button.title = 'Seek back 15 seconds'
    this.button.className = 'shaka-seek-back-15'
    parent.appendChild(this.button)

    const icon = new shaka.ui.Icon(null, SEEK_BACK_PATH)
    const svgEl = icon.getSvgElement()
    if (svgEl) {
      this.button.appendChild(svgEl)
    }

    const label = document.createElement('span')
    label.textContent = '15'
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
