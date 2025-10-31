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
  summary: string | null
  description: string | null | undefined
  category: string
  type: string | undefined
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
  description: string | null
  titles: string[] | null | undefined
  content: string | null | undefined
  summary: string | null | undefined
  season: string | null
  episode: string | null
  part: string | null
  captioned: boolean
  thumb: AvatarProps['src'] | undefined
  preview: string | null
  duration: number
  timestamp: string
  snapshot: number | null | undefined
  released: string | Date | null
  captions: Media[] | null
  tags: Tag[] | null
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
  progress: number | null
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
