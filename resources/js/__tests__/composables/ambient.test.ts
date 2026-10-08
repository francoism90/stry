import { useWashImages } from '@/composables/ambient'
import { describe, expect, it } from 'vite-plus/test'
import { ref } from 'vue'

describe('useWashImages', () => {
  it('picks the first thumbs and skips missing ones', () => {
    const images = useWashImages(['a.jpg', null, 'b.jpg', 'c.jpg'], 2)

    expect(images.value).toEqual(['a.jpg', 'b.jpg'])
  })

  it('keeps the images while earlier pages are merged in front of them', () => {
    const thumbs = ref(['e.jpg', 'f.jpg'])
    const images = useWashImages(thumbs, 2)

    expect(images.value).toEqual(['e.jpg', 'f.jpg'])

    thumbs.value = ['c.jpg', 'd.jpg', 'e.jpg', 'f.jpg']

    expect(images.value).toEqual(['e.jpg', 'f.jpg'])
  })

  it('picks new images once the current ones leave the list', () => {
    const thumbs = ref(['a.jpg', 'b.jpg'])
    const images = useWashImages(thumbs, 2)

    expect(images.value).toEqual(['a.jpg', 'b.jpg'])

    thumbs.value = ['x.jpg', 'a.jpg', 'y.jpg']

    expect(images.value).toEqual(['x.jpg', 'a.jpg'])
  })
})
