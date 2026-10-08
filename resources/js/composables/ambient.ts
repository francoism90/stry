import { computed, reactive, toValue, type MaybeRefOrGetter } from 'vue'

// Tracks which wash images have loaded so each can fade in. Server-rendered or cached images can
// finish loading before the load listener is attached, so `trackImage` also marks images that are
// already complete once their element is mounted.
export function useLoadedImages() {
  const loadedImages = reactive(new Set<string>())

  const isLoaded = (image: string): boolean => loadedImages.has(image)

  const markLoaded = (image: string): void => {
    loadedImages.add(image)
  }

  const trackImage =
    (image: string) =>
    (element: unknown): void => {
      if (element instanceof HTMLImageElement && element.complete && element.naturalWidth > 0) {
        markLoaded(image)
      }
    }

  return { isLoaded, markLoaded, trackImage }
}

// Picks the wash images from the first items of a list and keeps them while infinite scroll merges
// pages in. Opening a later page loads earlier pages in front of it, which would otherwise swap the
// wash on every scroll up; new images are only picked once the current ones leave the list.
export function useWashImages(thumbs: MaybeRefOrGetter<(string | null | undefined)[]>, count: number) {
  return computed<string[]>((previous) => {
    const available = toValue(thumbs).filter((thumb): thumb is string => typeof thumb === 'string')

    if (previous?.length && previous.every((image) => available.includes(image))) {
      return previous
    }

    return available.slice(0, count)
  })
}
