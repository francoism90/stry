import type { User } from '@/types'
import type { Page } from '@inertiajs/core'

declare module '@inertiajs/core' {
  interface PageProps {
    app: string
    locale: string
    location: string
    query: string
    flash: {
      message: string
      class: string
      level: string
    }
    auth: {
      user: User | undefined
    }
  }
}

declare module '@inertiajs/vue3' {
  export declare function usePage<T>(): Page<T>
}
