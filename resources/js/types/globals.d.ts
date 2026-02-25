import type { User } from '@/types'
import type { Page } from '@inertiajs/vue3'

declare module '@inertiajs/core' {
  export interface InertiaConfig {
    flashDataType: {
      label: string | undefined
      message: string | undefined
      type: 'success' | 'error'
      icon: string | undefined
      color: Toast['variants']['color'] | undefined
    }
  }

  export interface PageProps {
    readonly app: string
    readonly nonce: string
    readonly locale: string
    readonly auth: User | undefined
  }
}

declare module '@inertiajs/vue3' {
  export declare function usePage<T>(): Page<T>
}
