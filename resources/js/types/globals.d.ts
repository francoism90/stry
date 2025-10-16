import type { User } from '@/types'
import type { Page } from '@inertiajs/core'
import type { ToastProps } from '@nuxt/ui'

declare module '@inertiajs/core' {
  interface PageProps {
    readonly app: string
    readonly locale: string
    readonly location: string
    readonly root: string
    readonly path: string
    readonly query: string
    readonly flash: {
      message: string | undefined
      class: ToastProps['color'] | undefined
      level: 'info' | 'success' | 'warning' | 'error' | 'neutral' | undefined
    }
    readonly auth: {
      user: User | undefined
    }
  }
}

declare module '@inertiajs/vue3' {
  export declare function usePage<T>(): Page<T>
}
