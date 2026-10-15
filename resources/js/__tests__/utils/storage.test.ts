import { joinDiskPath, splitDiskPath } from '@/utils/storage'
import { describe, expect, it } from 'vite-plus/test'

const roots = ['/media', '/mnt', '/srv/media']

describe('splitDiskPath', () => {
  it('splits a path into its root and subfolder', () => {
    expect(splitDiskPath('/mnt/vault/backups', roots)).toEqual({ root: '/mnt', subfolder: 'vault/backups' })
  })

  it('returns an empty subfolder for a root itself', () => {
    expect(splitDiskPath('/srv/media', roots)).toEqual({ root: '/srv/media', subfolder: '' })
  })

  it('does not match a root that is only a prefix of a folder name', () => {
    expect(splitDiskPath('/mediafiles', roots)).toEqual({ root: undefined, subfolder: '/mediafiles' })
  })

  it('keeps a path outside every root as the subfolder', () => {
    expect(splitDiskPath('/etc', roots)).toEqual({ root: undefined, subfolder: '/etc' })
  })
})

describe('joinDiskPath', () => {
  it('joins a root and a subfolder, trimming slashes', () => {
    expect(joinDiskPath('/mnt', '/vault/')).toBe('/mnt/vault')
  })

  it('returns the root when the subfolder is empty', () => {
    expect(joinDiskPath('/media', '')).toBe('/media')
  })
})
