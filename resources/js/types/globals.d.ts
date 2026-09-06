import type { CollectionItem, EchoConfig, FlashData, OptionItem, User } from '@/types'
import type { Page } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import type Pusher from 'pusher-js'

declare global {
  interface Window {
    Pusher: typeof Pusher
  }
}

declare module '@inertiajs/core' {
  export interface InertiaConfig {
    readonly flashDataType: FlashData
  }

  export interface PageProps {
    readonly app: string
    readonly nonce: string
    readonly locale: string
    readonly locales: OptionItem[] | undefined
    readonly languages: OptionItem[] | undefined
    readonly tags: OptionItem[] | undefined
    readonly search: string | null | undefined
    readonly auth: User | undefined
    readonly echo: EchoConfig | undefined
    readonly collections: CollectionItem[] | undefined
    readonly unread: number
  }
}

declare module '@inertiajs/vue3' {
  export declare function usePage<T>(): Page<T>
}
