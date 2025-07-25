import type { User } from '@/types'
import type { Page } from '@inertiajs/core'

declare module '@inertiajs/core' {
  interface PageProps {
    readonly app: string
    readonly locale: string
    readonly location: string
    readonly query: string
    readonly flash: {
      message: string
      class: string
      level: string
    }
    readonly auth: {
      user: User | undefined
    }
  }
}

declare module '@inertiajs/vue3' {
  export declare function usePage<T>(): Page<T>
}
