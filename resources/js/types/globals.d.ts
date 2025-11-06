import type { User } from '@/types'
import type { Page } from '@inertiajs/core'
import type { SelectItem } from '@nuxt/ui'

declare module '@inertiajs/core' {
  export interface PageProps {
    readonly app: string
    readonly locale: string
    readonly auth: User | undefined
    readonly view?: string | undefined
    readonly filter?: string | undefined
    readonly filters?: SelectItem[] | undefined
    readonly search?: string | undefined
  }
}

declare module '@inertiajs/vue3' {
  export declare function usePage<T>(): Page<T>
}
