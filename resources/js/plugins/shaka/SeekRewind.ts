import iconSet from '@iconify-json/lucide/icons.json'
import shaka from 'shaka-player/dist/shaka-player.ui'

interface IconRecord {
  body: string
  width?: number
  height?: number
}

export class SeekRewind extends shaka.ui.Element {
  private button: HTMLButtonElement

  constructor(parent: HTMLElement, controls: shaka.ui.Controls) {
    super(parent, controls)

    this.button = document.createElement('button')
    this.button.type = 'button'
    this.button.title = 'Seek back 15 seconds'
    this.button.className = 'shaka-seek-back-15 hidden md:inline-flex'

    const iconData = (iconSet.icons as Record<string, IconRecord>)['rewind']
    if (iconData) {
      const width = iconData.width ?? iconSet.width
      const height = iconData.height ?? iconSet.height

      const svgNamespace = 'http://w3.org'
      const svg = document.createElementNS(svgNamespace, 'svg')

      svg.setAttribute('viewBox', `0 0 ${width} ${height}`)
      svg.setAttribute('fill', 'none')
      svg.setAttribute('stroke', 'currentColor')
      svg.setAttribute('stroke-width', '2')
      svg.setAttribute('stroke-linecap', 'round')
      svg.setAttribute('stroke-linejoin', 'round')
      svg.setAttribute('class', 'size-4.5 shrink-0 text-white')

      svg.innerHTML = iconData.body
      this.button.appendChild(svg)
    }

    const textLabel = document.createElement('span')
    textLabel.textContent = '15'
    textLabel.className = 'text-[11px] leading-none font-semibold text-white select-none'
    this.button.appendChild(textLabel)

    parent.appendChild(this.button)

    this.eventManager?.listen(this.button, 'click', () => {
      const player = this.controls?.getPlayer()
      const video = this.controls?.getVideo()

      if (player && video) {
        video.currentTime = Math.max(0, video.currentTime - 15)
      }
    })
  }

  override release(): void {
    super.release()
  }
}
