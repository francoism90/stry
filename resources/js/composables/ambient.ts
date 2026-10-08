import { computed, nextTick, reactive, toValue, watch, type MaybeRefOrGetter } from 'vue'

// Tracks which wash images have loaded so each can fade in. Server-rendered or cached images can
// finish loading before the load listener is attached, so the container is also checked for images
// that are already complete whenever the set of images changes.
export function useAmbientWash(
  images: MaybeRefOrGetter<string[]>,
  container: MaybeRefOrGetter<HTMLElement | null | undefined>,
) {
  const loadedImages = reactive(new Set<string>())

  const layerKey = computed(() => toValue(images).join('|'))

  const markLoaded = (image: string): void => {
    loadedImages.add(image)
  }

  const isLoaded = (image: string): boolean => loadedImages.has(image)

  watch(
    layerKey,
    async () => {
      await nextTick()

      toValue(container)
        ?.querySelectorAll<HTMLImageElement>('img[data-image]')
        .forEach((element) => {
          if (element.complete && element.naturalWidth > 0) {
            markLoaded(element.dataset.image!)
          }
        })
    },
    { immediate: true, flush: 'post' },
  )

  return { layerKey, isLoaded, markLoaded }
}
