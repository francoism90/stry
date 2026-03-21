import type { RemovableRef } from '@vueuse/core'
import { useLocalStorage } from '@vueuse/core'

export function useShakaStorage(): { muted: RemovableRef<boolean> } {
  const muted = useLocalStorage('shaka-muted', false)

  return { muted }
}
