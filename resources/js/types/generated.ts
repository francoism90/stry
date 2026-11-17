export type AppearanceSettings = {
  theme: string
  default_view: string
}
export type ApplicationSettings = {
  site_name: string
  timezone: string
  default_locale: Locale
  allow_registration: boolean
  max_profiles_per_user: number | null
  maintenance_message: string | null
}
export type ChapterSettings = {
  patterns: Record<string, string>
  default_type: ChapterType
}
export type ChapterType =
  | 'intro'
  | 'recap'
  | 'credits'
  | 'sponsor'
  | 'filler'
  | 'interaction_reminder'
  | 'scene'
  | 'main_event'
export type CursorPaginatedDataCollection<TKey, TValue> = CursorPaginator<TKey, TValue>
export type CursorPaginator<TKey, TValue> = {
  data: TKey extends string ? Record<TKey, TValue> : TValue[]
  links: {
    url: string | null
    label: string
    active: boolean
  }[]
  meta: {
    path: string
    per_page: number
    next_cursor: string | null
    next_page_url: string | null
    prev_cursor: string | null
    prev_page_url: string | null
  }
}
export type CursorPaginatorInterface<TKey, TValue> = CursorPaginator<TKey, TValue>
export type DiskContent =
  | 'videos'
  | 'captions'
  | 'conversions'
  | 'thumbnails'
  | 'reels'
  | 'transcodes'
  | 'import'
  | 'exports'
  | 'snapshots'
export type DiskDriver = 'local' | 's3'
export type GeneralSettings = {
  timezone: string
  locale: Locale
  language: Language
  date_format: string
  time_format: string
}
export type GroupScope = 'all' | 'custom' | 'mixer'
export type GroupSorter = 'name' | 'videos' | 'newest' | 'oldest' | 'updated'
export type GroupType = 'custom' | 'liked' | 'mixer' | 'saved' | 'viewed'
export type Language = 'en' | 'nl'
export type LengthAwarePaginator<TKey, TValue> = {
  data: TKey extends string ? Record<TKey, TValue> : TValue[]
  links: {
    url: string | null
    label: string
    active: boolean
  }[]
  meta: {
    total: number
    current_page: number
    first_page_url: string
    from: number | null
    last_page: number
    last_page_url: string
    next_page_url: string | null
    path: string
    per_page: number
    prev_page_url: string | null
    to: number | null
  }
}
export type LengthAwarePaginatorInterface<TKey, TValue> = LengthAwarePaginator<TKey, TValue>
export type Locale = 'en-US' | 'nl-NL'
export type PaginatedDataCollection<TKey, TValue> = LengthAwarePaginator<TKey, TValue>
export type PlaybackSettings = {
  text_language: Language
  encryption: boolean
  refresh_before: number
  completion_threshold: number
  renditions: number[]
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
export type ProcessingSettings = {
  extract_captions: boolean
  extract_chapters: boolean
  extract_storyboard: boolean
  create_renditions: boolean
  warm_streams: boolean
  import_batch_size: number
}
export type ProfileScope = 'all' | 'kids' | 'primary'
export type ProfileSorter = 'name' | 'newest' | 'oldest'
export type ReelSettings = {
  enabled: boolean
  width: number
  height: number
  fps: number
  fit: 'fill' | 'blur' | 'letterbox'
  zoom: number
  codec: 'h264' | 'hevc' | 'av1'
  hardware: boolean
  quality: number
  cuts: number
  cut_duration: number
  duration: number
  short_videos: boolean
  short_duration: number
  scene_threshold: number
}
export type ShuffleType = 'videos' | 'tags'
export type SnapshotFrequency = 'never' | 'daily' | 'weekly' | 'monthly'
export type SnapshotSettings = {
  frequency: SnapshotFrequency
  keep: number
  compress: boolean
}
export type StorageDisk = {
  name: string
  label: string
  driver: DiskDriver
  path: string
  visibility: 'public' | 'private'
  serve: boolean
  contents: string[]
  default_for: string[]
  bucket: string | null
  region: string | null
  endpoint: string | null
  url: string | null
  temporary_url: string | null
  use_path_style_endpoint: boolean | null
  cache_control: string | null
}
export type TagScope = 'all' | 'serie' | 'studio' | 'genre' | 'person' | 'language'
export type TagSorter = 'videos' | 'newest' | 'oldest'
export type TagType = 'serie' | 'studio' | 'genre' | 'person' | 'language'
export type TranscodeEncoder = 'av1'
export type TranscodeScope = 'all' | 'pending' | 'processing' | 'completed' | 'failed'
export type TranscodeSorter = 'newest' | 'oldest' | 'updated'
export type UserLocale = 'en' | 'nl'
export type UserScope = 'all' | 'verified' | 'unverified' | 'deleted'
export type UserSettings = {
  general?: GeneralSettings | null
  appearance?: AppearanceSettings | null
  player?: PlayerSettings | null
}
export type UserSorter = 'newest' | 'oldest'
export type VideoFile = {
  disk: string
  path: string
  name: string | null
  size: number | null
}
export type VideoLibraryScope = 'all' | 'verified' | 'pending' | 'failed' | 'deleted'
export type VideoScope = 'all' | 'watching' | 'shorts' | 'unseen' | 'untagged' | 'captions'
export type VideoSorter = 'recommended' | 'newest' | 'oldest' | 'longest' | 'shortest' | 'ordered' | 'filesize'
