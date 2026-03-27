import type { FlashType } from '@/types'
import { router } from '@inertiajs/vue3'
import { createSharedComposable, tryOnUnmounted } from '@vueuse/core'

const typeMap: Record<FlashType, { icon: string; color: FlashType }> = {
  success: { icon: 'i-lucide-circle-check', color: 'success' },
  error: { icon: 'i-lucide-circle-x', color: 'error' },
  warning: { icon: 'i-lucide-triangle-alert', color: 'warning' },
  info: { icon: 'i-lucide-info', color: 'info' },
  primary: { icon: 'i-lucide-info', color: 'primary' },
}

function resolveType(type?: string): { icon: string; color: FlashType } {
  return typeMap[(type as FlashType) ?? ''] ?? typeMap.info
}

export const useFlash = createSharedComposable(() => {
  const toast = useToast()

  let isPartialReload = false

  const unsubBefore = router.on('before', (event) => {
    isPartialReload = event.detail.visit.only.length > 0
  })

  const unsubFinish = router.on('finish', () => {
    isPartialReload = false
  })

  const unsubFlash = router.on('flash', (event) => {
    if (event.detail.flash && !isPartialReload) {
      const { icon, color } = resolveType(event.detail.flash.type)

      toast.add({
        title: event.detail.flash.title ?? 'Notice',
        description: event.detail.flash.description ?? 'No message provided.',
        color,
        icon,
      })
    }
  })

  tryOnUnmounted(() => {
    unsubBefore()
    unsubFinish()
    unsubFlash()
  })
})
