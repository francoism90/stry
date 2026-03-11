import type { User } from '@/types'
import type { Page } from '@inertiajs/vue3'
import type { ToastProps } from '@nuxt/ui'

declare module '@inertiajs/core' {
  export interface InertiaConfig {
    readonly flashDataType: ToastProps
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
