import { router } from '@inertiajs/vue3'
import { onUnmounted } from 'vue'

export function useNotifications() {
  const toast = useToast()

  onUnmounted(
    router.on('flash', (event) => {
      if (event.detail.flash) {
        toast.add({
          title: event.detail.flash.title ?? 'Notice',
          description: event.detail.flash.description ?? 'No message provided.',
          color: event.detail.flash.color ?? 'primary',
          icon: event.detail.flash.icon ?? 'i-lucide-info',
        })
      }
    }),
  )
}
