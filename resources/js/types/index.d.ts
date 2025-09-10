import type { SelectMenuItem } from '@nuxt/ui'
import type { PlayerSrc } from 'vidstack'

export type Model = {
  id: string
  created_at: string
  updated_at: string
}

export type User = Model & {
  name: string
  email: string
  email_verified_at: string | null
  avatar?: string | null
  roles?: string[]
  permissions?: string[]
}

export type Media = Model & {
  name: string
  asset: string
  mime_type: string
  size: number
}

export type Tag = Model & {
  name: string
  description: string | null
  category: string
  type: string
  thumbnail: string
  srcset: string
  videos?: number
  related?: Tag[] | null
}

export type Tags = Omit<Paginator, 'data'> & {
  data: Tag[] | null
}

export type TagMenuItem = Tag & SelectMenuItem

export type Playlist = Model & {
  asset: PlayerSrc
  valid: boolean
  percent: number | null
  state: string
  expires_at: string | null
}

export type Video = Model & {
  user?: User
  name: string
  title: string
  titles?: string[] | null
  content?: string | null
  summary: string | null
  season: string | null
  episode: string | null
  part: string | null
  captions: boolean | null
  thumbnail: string
  srcset: string
  preview: string | null
  duration: number | null
  timestamp: string | null
  snapshot: number | string | null
  released_at: string | null
  tags: Tag[] | null
  state: string
  expires_at: string | null
  published_at: string | null
}

export type Videos = Omit<Paginator, 'data'> & {
  data: Video[] | null
}

export type Playlist = Model & {
  asset: string
  valid: boolean
  percent: number | null
  type: string | null
  state: string
  accessed_at: string | null
  expires_at: string | null
  transcoded_at: string | null
}

export type Playlists = Omit<Paginator, 'data'> & {
  data: Playlist[] | null
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
  next_cursor?: string | null
  prev_cursor?: string | null
}
