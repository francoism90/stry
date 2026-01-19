import type { AvatarProps, SelectMenuItem } from '@nuxt/ui'
import type { PlayerSrc } from 'vidstack'

export type Model = {
  id: string
  created_at: string
  updated_at: string
}

export type User = Model & {
  name: string
  email: string | undefined
  avatar: AvatarProps['src'] | null | undefined
  roles: string[] | undefined
  permissions: string[] | undefined
}

export type UserCollection = Omit<Paginator, 'data'> & {
  data: User[] | undefined
}

export type Media = Model & {
  asset: string | undefined
  name: string
  file_name: string
  mime_type: string
  size: number
  file_size: string
  collection_name: string
  disk: string
  conversions_disk: string
}

export type MediaCollection = Omit<Paginator, 'data'> & {
  data: Media[] | undefined
}

export type Tag = Model & {
  name: string
  summary: string | undefined
  description: string | undefined
  category: string
  type: string | undefined
  adult: boolean
  thumb: AvatarProps['src'] | null | undefined
  related: Tag[] | null | undefined
  videos: number | undefined
}

export type TagCollection = Omit<Paginator, 'data'> & {
  data: Tag[] | undefined
}

export type TagMenuItem = Tag & SelectMenuItem

export type Video = Model & {
  user: User | undefined
  name: string
  title: string
  description: string | undefined
  titles: string[] | undefined
  content: string | undefined
  summary: string | undefined
  season: string | null
  episode: string | null
  part: string | null
  adult: boolean
  captioned: boolean
  thumb: string | undefined
  duration: number
  timestamp: string
  filesize: string | undefined
  snapshot: number | undefined
  tags: Tag[] | null
  expires_at: string | undefined
  liked: boolean | undefined
  saved: boolean | undefined
  viewed: boolean | undefined
  published_at: string | undefined
  released_at: string | undefined
  state: string
}

export type VideoCollection = Omit<Paginator, 'data'> & {
  data: Video[] | undefined
}

export type Playlist = Model & {
  asset: PlayerSrc | null
  expired: boolean
  failed: boolean
  valid: boolean
  type: string | undefined
  expires_at: string | null | undefined
  state: string
}

export type PlaylistCollection = Omit<Paginator, 'data'> & {
  data: Playlist[] | undefined
}

export type Paginator = {
  data: Model[] | undefined
  links: {
    first: string | undefined
    last: string | undefined
    prev: string | undefined
    next: string | undefined
  }
  meta: {
    current_page: number
    current_page_url: string
    from: number | undefined
    path: string
    per_page: number
    to: number | undefined
    total: number
  }
}
