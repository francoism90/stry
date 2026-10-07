import { isReelLoaded, reelPreload } from '@/composables/reels'
import { describe, expect, it } from 'vite-plus/test'

describe('isReelLoaded', () => {
  it('keeps the previous reel and the next two loaded around the active one', () => {
    const loaded = [0, 1, 2, 3, 4, 5, 6].filter((index) => isReelLoaded(index, 3))

    expect(loaded).toEqual([2, 3, 4, 5])
  })
})

describe('reelPreload', () => {
  it('preloads the active reel and the next two only', () => {
    const preloads = [2, 3, 4, 5, 6].map((index) => reelPreload(index, 3))

    expect(preloads).toEqual(['none', 'auto', 'auto', 'auto', 'none'])
  })
})
