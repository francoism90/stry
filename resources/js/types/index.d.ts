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
  summary: string
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
  description: string
  titles: string[] | null | undefined
  content: string | null | undefined
  summary: string | null | undefined
  season: string | null
  episode: string | null
  part: string | null
  captioned: boolean
  thumb: AvatarProps['src'] | undefined
  duration: number
  timestamp: string
  snapshot: number | null | undefined
  captions: Media[] | null
  tags: Tag[] | null
  expires_at: string | undefined
  published_at: string | undefined
  released_at: string | undefined
  state: string
}

export type VideoCollection = Omit<Paginator, 'data'> & {
  data: Video[] | null
}

export type Playlist = Model & {
  asset: PlayerSrc | null
  valid: boolean
  percent: number | null
  type: string
  accessed_at: string | null | undefined
  expires_at: string | null | undefined
  transcoded_at: string | null | undefined
  state: string
}

export type PlaylistCollection = Omit<Paginator, 'data'> & {
  data: Playlist[] | null
}

export type Paginator = {
  data: Model[] | null | undefined
  page?: number | null
  next_cursor?: string | null
  prev_cursor?: string | null
}

export type PageView = 'horizontal' | 'vertical' | undefined
