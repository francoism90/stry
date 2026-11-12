/// <reference types="vite/client" />

declare module 'virtual:lucide-icons' {
  import type { IconifyJSON } from '@iconify/vue'

  const collection: IconifyJSON
  export default collection
}
