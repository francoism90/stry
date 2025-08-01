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

export type Links = {
  first: string | null
  last: string | null
  prev: string | null
  next: string | null
}

export type Page = {
  from?: number | null
  per_page?: number | null
  to?: number | null
  current_page?: number | null
  current_page_url?: string | null
  first_page_url?: string | null
  last_page_url?: string | null
  next_page_url?: string | null
  prev_page_url?: string | null
}
