import type { EchoConfig, FlashData, User } from '@/types'
import type { Page } from '@inertiajs/vue3'
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
    readonly search: string | null
    readonly auth: User | undefined
    readonly echo: EchoConfig | undefined
  }
}

declare module '@inertiajs/vue3' {
  export declare function usePage<T>(): Page<T>
}
