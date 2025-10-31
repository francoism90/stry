import type { AvatarProps, SelectMenuItem } from '@nuxt/ui'
import type { PlayerSrc } from 'vidstack'

export type Model = {
  id: string
  created_at: string
  updated_at: string
}

export type User = Model & {
  name: string
  email: string
  email_verified_at: string | null | undefined
  avatar: AvatarProps['src'] | null | undefined
  roles: string[] | undefined
  permissions: string[] | undefined
}

export type Media = Model & {
  asset: string | undefined
  name: string
  mime_type: string
  file_size: string
}

export type MediaCollection = Omit<Paginator, 'data'> & {
  data: Media[] | null
}

export type Tag = Model & {
  name: string
  description: string | null
  category: string
  type: string
  thumb: AvatarProps['src'] | null | undefined
  related: Tag[] | null | undefined
  videos: number | undefined
}

export type TagCollection = Omit<Paginator, 'data'> & {
  data: Tag[] | null
}

export type TagMenuItem = Tag & SelectMenuItem

export type Video = Model & {
  user: User | undefined
  name: string
  title: string
  titles: string[] | null | undefined
  content: string | null | undefined
  summary: string | null | undefined
  season: string | null | undefined
  episode: string | null | undefined
  part: string | null | undefined
  captions: boolean | null | undefined
  thumb: AvatarProps['src'] | undefined
  preview: string | undefined
  duration: number | undefined
  timestamp: string | undefined
  snapshot: number | undefined
  released: string | undefined
  tags: Tag[] | undefined
  state: string
  expires_at: string | undefined
  published_at: string | undefined
  released_at: string | undefined
}

export type VideoCollection = Omit<Paginator, 'data'> & {
  data: Video[] | null
}

export type Playlist = Model & {
  asset: PlayerSrc | null
  valid: boolean
  percent: number | null
  type: string
  state: string
  accessed_at: string | null | undefined
  expires_at: string | null | undefined
  transcoded_at: string | null | undefined
}

export type PlaylistCollection = Omit<Paginator, 'data'> & {
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
  next_cursor: string | null | undefined
  prev_cursor: string | null | undefined
}
