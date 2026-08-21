import { useEventListener } from '@vueuse/core'
import { computed, ref } from 'vue'

// Infinite-scroll content can nest its own scrolling ancestor, so listening on
// window alone would miss it. Scroll events don't bubble, but they do reach a
// capturing listener on window, letting us read whichever element actually scrolled.
export function useHeaderCollapse(offset = 100) {
  const scrollOffset = ref(0)

  useEventListener(
    window,
    'scroll',
    (event) => {
      const target = event.target as Document | HTMLElement
      scrollOffset.value = target instanceof Document ? (target.defaultView?.scrollY ?? 0) : target.scrollTop
    },
    { capture: true, passive: true },
  )

  const isHeaderCollapsed = computed(() => scrollOffset.value > offset)

  return { isHeaderCollapsed }
}
