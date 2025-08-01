import type { SelectMenuItem } from '@nuxt/ui'

export type User = {
  id: string
  name: string
  email: string
  email_verified: string
  avatar?: string
  roles?: string[]
  permissions?: string[]
  state: string
  created: string
  updated: string
}

export type Tag = {
  id: string
  name: string
  description: string
  type: string
  videos: number
  created: string
  updated: string
}

export type Tags = Omit<Paginator, 'data'> & {
  data: Tag[] | null
}

export type TagMenuItem = Tag & SelectMenuItem

export type Playlist = {
  id: string
  asset: string
  valid: boolean
  state: string
  expires: string
  created: string
  updated: string
}

export type Video = {
  id: string
  user?: User
  name: string
  titles?: string[]
  content?: string | null
  summary: string | null
  season: string | null
  episode: string | null
  part: string | null
  thumbnail: string
  srcset: string
  duration: number
  timestamp: string
  snapshot: number
  released: string
  expires: string
  published: string
  state: string
  created: string
  updated: string
  tags: Tag[] | null
}

export type Videos = Omit<Paginator, 'data'> & {
  data: Video[] | null
}

export type Paginator = {
  data: object[] | null
  per_page: number | null
  current_page: number | null
  from: number | null
  to: number | null
  path: string
  current_page_url: string | null
  first_page_url: string | null
  next_page_url: string | null
  prev_page_url: string | null
}
