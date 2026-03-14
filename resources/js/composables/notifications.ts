import type { Notification, NotificationCollection } from '@/types'
import { router } from '@inertiajs/vue3'
import { computed, onUnmounted, type Ref } from 'vue'

export function useNotifications(notifications?: Ref<NotificationCollection>) {
  const toast = useToast()

  const unsub = router.on('flash', (event) => {
    if (event.detail.flash) {
      toast.add({
        title: event.detail.flash.title ?? 'Notice',
        description: event.detail.flash.description ?? 'No message provided.',
        color: event.detail.flash.color ?? 'primary',
        icon: event.detail.flash.icon ?? 'i-lucide-info',
      })
    }
  })

  const hasUnread = computed(() => notifications?.value?.data?.some((n) => !n.read_at) ?? false)

  const getTitle = (notification: Notification): string =>
    (notification.data.title as string | undefined) ?? notification.type

  const getMessage = (notification: Notification): string | undefined => notification.data.message as string | undefined

  const toggleRead = (notification: Notification): void => {
    router.patch(
      `/notifications/${notification.id}`,
      {},
      {
        preserveScroll: true,
        only: ['notifications'],
      },
    )
  }

  const remove = (notification: Notification): void => {
    router.delete(`/notifications/${notification.id}`, {
      preserveScroll: true,
      only: ['notifications'],
    })
  }

  const markAllAsRead = (): void => {
    router.post(
      '/api/v1/notifications/mark-all-read',
      {},
      {
        preserveScroll: true,
        only: ['notifications'],
      },
    )
  }

  onUnmounted(unsub)

  return { hasUnread, getTitle, getMessage, toggleRead, remove, markAllAsRead }
}
