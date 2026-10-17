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
