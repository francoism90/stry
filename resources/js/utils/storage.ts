import type { StorageDisk } from '@/types'

export const newStorageDisk = (path = ''): StorageDisk => ({
  name: '',
  label: '',
  driver: 'local',
  path,
  visibility: 'private',
  serve: false,
  contents: [],
  default_for: [],
  bucket: null,
  region: null,
  endpoint: null,
  url: null,
  temporary_url: null,
  use_path_style_endpoint: null,
  cache_control: null,
  has_secret: false,
  key: null,
  secret: null,
})

/**
 * Splits a local disk path into the root it's in and the subfolder below it, using the longest
 * matching root. A path outside every root keeps its full value as the subfolder.
 */
export const splitDiskPath = (path: string, roots: string[]): { root: string | undefined; subfolder: string } => {
  const root = [...roots]
    .sort((a, b) => b.length - a.length)
    .find((candidate) => path === candidate || path.startsWith(`${candidate}/`))

  if (!root) {
    return { root: undefined, subfolder: path }
  }

  return { root, subfolder: path.slice(root.length).replace(/^\/+/, '') }
}

export const joinDiskPath = (root: string | undefined, subfolder: string): string => {
  const trimmed = subfolder.replace(/^\/+|\/+$/g, '')

  if (!root) {
    return trimmed
  }

  return trimmed ? `${root}/${trimmed}` : root
}
