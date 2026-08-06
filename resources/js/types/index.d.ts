import type { AvatarProps, BadgeProps, SelectMenuItem } from '@nuxt/ui'

export type EchoConfig = {
  readonly key: string
  readonly host: string
  readonly port: number
  readonly scheme: string
}

export type FlashType = 'success' | 'error' | 'warning' | 'info' | 'primary'

export type FlashData = {
  readonly title?: string
  readonly description?: string
  readonly type?: FlashType
}

export type VideoFilters = {
  captioned?: string | boolean
  shorts?: string | boolean
  unseen?: string | boolean
  untagged?: string | boolean
  tagged?: string
  state?: string
  season?: string
  episode?: string
  part?: string
}

export type FilterOption = {
  label: string
  value: string
}

export type Model = {
  id: string
  created_at: string
  updated_at: string
}

export type ModelResource = Model & {
  subject?: string
  name?: string
  label?: string
  slug?: string
}

export type ModelState = {
  name: string
  label: string
  icon: string
  color: BadgeProps['color']
}

export type User = Model & {
  name: string
  email?: string
  avatar?: AvatarProps['src'] | null
  roles?: string[] | null
  permissions?: string[] | null
  settings?: UserSettings
  videos_count?: number
  email_verified_at?: string | null
}

export type UserCollection = Omit<Paginator, 'data'> & {
  data: User[] | undefined
}

export type Profile = Model & {
  name: string
  avatar: string | null
  is_kids: boolean
  is_primary: boolean
  state: ModelState
}

export type ProfileCollection = Omit<Paginator, 'data'> & {
  data: Profile[] | undefined
}

export type UserSettings = {
  general: GeneralSettings
  appearance: AppearanceSettings
  player: PlayerSettings
}

export type GeneralSettings = {
  timezone: string
  locale: string
  language: string
  date_format: string
  time_format: string
}

export type AppearanceSettings = {
  theme: string
  default_view: string
}

export type PlayerSettings = {
  autoplay: boolean
  muted: boolean
  volume: number
  loop: boolean
  captions: boolean
  quality: string
  playback_speed: number
  audio_language: string
  caption_language: string
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
  name: string
  url?: string | null
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

export type Tag = Model & {
  name: string
  slug: string
  summary: string | null
  description?: string
  category: string
  type: string | null
  adult: boolean
  thumb?: AvatarProps['src'] | null
  related?: Tag[]
  videos?: number
}

export type TagCollection = Omit<Paginator, 'data'> & {
  data: Tag[] | undefined
}

export type TagMenuItem = Tag & SelectMenuItem

export type Video = Model & {
  user?: User
  tags?: Tag[]
  name: string
  title: string
  titles?: string
  description: string | null
  content?: string
  summary?: string
  identifier: string | null
  season: string | null
  episode: string | null
  part: string | null
  released: string | null
  duration: number | null
  timestamp: string | null
  filesize?: string
  snapshot?: number
  thumb: string | null
  thumb_srcset: string | null
  adult: boolean
  captioned: boolean
  liked: boolean | null
  saved: boolean | null
  viewed: boolean | null
  expires_at: string | null
  published_at: string | null
  released_at: string | null
  state: ModelState
}

export type VideoCollection = Omit<Paginator, 'data'> & {
  data: Video[] | undefined
}

export type Playlist = Model & {
  resource?: ModelResource
  asset: string | null
  asset_refresh_in: number
  encryption_key_id: string | null
  encryption_key: string | null
  expired: boolean
  failed: boolean
  valid: boolean
  type: string | null
  expires_at: string | null
  state: ModelState
}

export type PlaylistCollection = Omit<Paginator, 'data'> & {
  data: Playlist[] | undefined
}

export type Transcode = Model & {
  resource?: ModelResource
  encoder: string
  processing: boolean
  completed: boolean
  failed: boolean
  size: number
  file_size: string
  error_message: string | null
  retry_count: number
  started_at: string | null
  transcoded_at: string | null
  state: ModelState
}

export type TranscodeCollection = Omit<Paginator, 'data'> & {
  data: Transcode[] | undefined
}

export type Group = Model & {
  name: string
  title: string
  content: string | null
  type: string | null
  state: ModelState
  videos?: number
  has?: boolean
}

export type GroupCollection = Omit<Paginator, 'data'> & {
  data: Group[] | undefined
}

export type Notification = {
  id: string
  type: string
  data: Record<string, unknown>
  read_at: string | null
  created_at: string
  updated_at: string
}

export type NotificationCollection = Omit<Paginator, 'data'> & {
  data: Notification[] | undefined
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
