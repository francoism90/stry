import { isPlaylistReplacement } from '@/composables/playlist'
import type { Playlist } from '@/types'
import { describe, expect, it } from 'vitest'

const playlist = (id: string, asset: string): Playlist => ({ id, asset }) as Playlist

describe('isPlaylistReplacement', () => {
  it('is not a replacement when id and asset are unchanged', () => {
    const current = playlist('1', 'https://example.test/a?expires=1')

    expect(isPlaylistReplacement(playlist('1', 'https://example.test/a?expires=1'), current)).toBe(false)
  })

  it('is a replacement when the id changes', () => {
    const current = playlist('1', 'https://example.test/a?expires=1')

    expect(isPlaylistReplacement(playlist('2', 'https://example.test/a?expires=1'), current)).toBe(true)
  })

  it('is a replacement when only the asset (a re-signed url) changes', () => {
    const current = playlist('1', 'https://example.test/a?expires=1')

    expect(isPlaylistReplacement(playlist('1', 'https://example.test/a?expires=2'), current)).toBe(true)
  })

  it('is not a replacement when there is nothing loaded yet', () => {
    expect(isPlaylistReplacement(null, null)).toBe(false)
  })
})
