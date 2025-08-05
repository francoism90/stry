import type { SelectMenuItem } from '@nuxt/ui'
import type { PlayerSrc } from 'vidstack'

export type Model = {
  id: string
  created: string
  updated: string
}

export type User = Model & {
  name: string
  email: string
  email_verified: string | null
  avatar?: string | null
  roles?: string[]
  permissions?: string[]
  state: string
}

export type Tag = Model & {
  name: string
  description: string | null
  type: string
  thumbnail: string
  srcset: string
  videos?: number
}

export type Tags = Omit<Paginator, 'data'> & {
  data: Tag[] | null
}

export type TagMenuItem = Tag & SelectMenuItem

export type Playlist = Model & {
  asset: PlayerSrc
  valid: boolean
  state: string
  expires: string | null
}

export type Video = Model & {
  user?: User
  name: string
  titles?: string[] | null
  content?: string | null
  summary: string | null
  season: string | null
  episode: string | null
  part: string | null
  thumbnail: string
  srcset: string
  preview: string
  duration: number | null
  timestamp: string | null
  snapshot: number | null
  released: string | null
  expires: string | null
  published: string | null
  state: string
  tags: Tag[] | null
}

export type Videos = Omit<Paginator, 'data'> & {
  data: Video[] | null
}

export type Paginator = {
  data: Model[] | null
  per_page: number | null
  current_page: number | null
  from: number | null
  to: number | null
  path: string
  current_page_url: string | null
  first_page_url: string | null
  next_page_url: string | null
  prev_page_url: string | null
  next_cursor: string | null
  prev_cursor: string | null
}

export type DetailListItem = {
  label: string
  value?: unknown
  description?: string
  icon?: string
  hidden?: boolean
  to?: string
}
