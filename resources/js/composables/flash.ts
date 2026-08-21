import type { FlashData, FlashType } from '@/types'
import { router } from '@inertiajs/vue3'
import { useToast } from '@nuxt/ui/composables'
import { createSharedComposable, tryOnUnmounted } from '@vueuse/core'

const typeMap: Record<FlashType, { icon: string; color: FlashType }> = {
  success: { icon: 'i-lucide-circle-check', color: 'success' },
  error: { icon: 'i-lucide-circle-x', color: 'error' },
  warning: { icon: 'i-lucide-triangle-alert', color: 'warning' },
  info: { icon: 'i-lucide-info', color: 'info' },
  primary: { icon: 'i-lucide-info', color: 'primary' },
}

export const useFlash = createSharedComposable(() => {
  const toast = useToast()

  const resolveType = (type?: string): { icon: string; color: FlashType } => {
    return typeMap[(type as FlashType) ?? ''] ?? typeMap.info
  }

  const unsubFlash = router.on('flash', (event) => {
    const flash = (event.detail?.flash ?? {}) as FlashData
    const { icon, color } = resolveType(flash.type)

    toast.add({
      title: flash.title ?? 'Notice',
      description: flash.description ?? 'No message provided.',
      color,
      icon,
    })
  })

  tryOnUnmounted(() => {
    unsubFlash()
  })

  return {
    resolveType,
  }
})
