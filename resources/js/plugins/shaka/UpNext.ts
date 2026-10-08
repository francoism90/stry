import shaka from 'shaka-player/dist/shaka-player.ui'
import { iconDataUrl } from './icons'

// Shaka's own skip_next button only follows its queue manager, while the Up next queue lives in
// Inertia props. This button bubbles a `skipnext` DOM event up to VideoPlayer instead, and stays
// hidden until VideoPlayer marks the container with `data-has-next`.
export class UpNext extends shaka.ui.Element {
  private button: HTMLButtonElement

  constructor(parent: HTMLElement, controls: shaka.ui.Controls) {
    super(parent, controls)

    this.button = document.createElement('button')
    this.button.type = 'button'
    this.button.ariaLabel = 'Play next video'
    this.button.className = 'shaka-skip-next-button shaka-tooltip shaka-no-propagation'
    parent.appendChild(this.button)

    const icon = new shaka.ui.Icon(null, { url: iconDataUrl('skip-forward'), size: 24, path: null, viewBox: null })
    const svgEl = icon.getSvgElement()
    if (svgEl) {
      this.button.appendChild(svgEl)
    }

    this.eventManager?.listen(this.button, 'click', () => {
      // Matches Shaka's skip buttons: a tap on hidden controls only reveals them.
      if (!this.controls?.isOpaque()) {
        return
      }

      this.button.dispatchEvent(new CustomEvent('skipnext', { bubbles: true }))
    })
  }

  override release(): void {
    super.release()
  }
}
