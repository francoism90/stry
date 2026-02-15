import type { AvatarProps, SelectMenuItem } from '@nuxt/ui'

export type Model = {
  id: string
  created_at: string
  updated_at: string
}

export type ModelState = {
  name: string
  label: string
  icon: string
  color: Badge['variants']['color']
}

export type FilterOption = {
  label: string
  value: string
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

export type MediaStream = {
  index: number
  width: number
  height: number
  bit_rate: string
  duration: string
  codec_name: string
  codec_type: string
  closed_captions: number
}

export type MediaCustomProperties = {
  streams: MediaStream[]
  [key: string]: unknown
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
  custom_properties?: MediaCustomProperties | null
  generated_conversions?: Record<string, unknown> | null
  responsive_images?: Record<string, unknown> | null
}

export type MediaCollection = Omit<Paginator, 'data'> & {
  data: Media[] | undefined
}

export type Transcode = Model & {
  video_id: number
  encoder: string
  pending: boolean
  processing: boolean
  completed: boolean
  failed: boolean
  state: ModelState
  file_size: number
  file_size_human: string
  error_message: string | null
  retry_count: number
  started_at: string | null
  transcoded_at: string | null
}

export type Tag = Model & {
  name: string
  slug: string
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
  tags: Tag[] | undefined
  name: string
  title: string
  titles: string[] | undefined
  description: string | undefined
  content: string | undefined
  summary: string | undefined
  identifier: string | undefined
  season: string | null
  episode: string | null
  part: string | null
  released: string | undefined
  duration: number | undefined
  timestamp: string | undefined
  filesize: string | undefined
  snapshot: number | undefined
  thumb: string | undefined
  adult: boolean | undefined
  captioned: boolean | undefined
  liked: boolean | undefined
  saved: boolean | undefined
  viewed: boolean | undefined
  expires_at: string | undefined
  published_at: string | undefined
  released_at: string | undefined
  state: ModelState
}

export type VideoCollection = Omit<Paginator, 'data'> & {
  data: Video[] | undefined
}

export type Playlist = Model & {
  asset: string | null
  encryption_key_id: string | null
  encryption_key: string | null
  expired: boolean
  failed: boolean
  valid: boolean
  type: string | undefined
  expires_at: string | null | undefined
  state: ModelState
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
