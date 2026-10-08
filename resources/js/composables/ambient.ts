import { computed, nextTick, reactive, ref, toValue, watch, type MaybeRefOrGetter } from 'vue'

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

// Picks the wash images from the first items of a list and keeps them while infinite scroll merges
// pages in. Opening a later page loads earlier pages in front of it, which would otherwise swap the
// wash on every scroll up; new images are only picked once the current ones leave the list.
export function useWashImages(thumbs: MaybeRefOrGetter<(string | null | undefined)[]>, count: number) {
  const images = ref<string[]>([])

  watch(
    () => toValue(thumbs).filter((thumb): thumb is string => typeof thumb === 'string'),
    (available) => {
      const isStillListed = images.value.length > 0 && images.value.every((image) => available.includes(image))

      if (!isStillListed) {
        images.value = available.slice(0, count)
      }
    },
    { immediate: true },
  )

  return images
}
