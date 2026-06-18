import iconSet from '@iconify-json/lucide/icons.json'
import shaka from 'shaka-player/dist/shaka-player.ui'

interface IconRecord {
  body: string
  width?: number
  height?: number
}

export class SeekForward extends shaka.ui.Element {
  private button: HTMLButtonElement

  constructor(parent: HTMLElement, controls: shaka.ui.Controls) {
    super(parent, controls)

    this.button = document.createElement('button')
    this.button.type = 'button'
    this.button.title = 'Seek forward 30 seconds'
    this.button.className = 'shaka-seek-forward-15 hidden md:inline-flex'

    const iconData = (iconSet.icons as Record<string, IconRecord>)['fast-forward']
    if (iconData) {
      const width = iconData.width ?? iconSet.width
      const height = iconData.height ?? iconSet.height

      const svgNamespace = 'http://w3.org'
      const svg = document.createElementNS(svgNamespace, 'svg')
      svg.setAttribute('viewBox', `0 0 ${width} ${height}`)
      svg.innerHTML = iconData.body

      this.button.appendChild(svg)
    }

    const textLabel = document.createElement('span')
    textLabel.textContent = '30'
    this.button.appendChild(textLabel)

    parent.appendChild(this.button)

    this.eventManager?.listen(this.button, 'click', () => {
      const player = this.controls?.getPlayer()
      const video = this.controls?.getVideo()

      if (player && video) {
        video.currentTime = Math.min(video.duration || Infinity, video.currentTime + 30)
      }
    })
  }

  override release(): void {
    super.release()
  }
}
