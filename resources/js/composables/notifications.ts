import MarkAllNotificationsReadController from '@/actions/App/Api/Notifications/Controllers/MarkAllNotificationsReadController'
import type { FlashType, NotificationCollection, Notification as NotificationModel } from '@/types'
import { router } from '@inertiajs/vue3'
import { computed, onUnmounted, type Ref } from 'vue'

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

export function useNotifications(notifications?: Ref<NotificationCollection>) {
  const toast = useToast()

  const unsub = router.on('flash', (event) => {
    if (event.detail.flash) {
      const { icon, color } = resolveType(event.detail.flash.type)

      toast.add({
        title: event.detail.flash.title ?? 'Notice',
        description: event.detail.flash.description ?? 'No message provided.',
        color,
        icon,
      })
    }
  })

  const hasUnread = computed(() => notifications?.value?.data?.some((n: NotificationModel) => !n.read_at) ?? false)

  const getTitle = (notification: NotificationModel): string =>
    (notification.data.title as string | undefined) ?? notification.type

  const getMessage = (notification: NotificationModel): string | undefined =>
    notification.data.message as string | undefined

  const toggleRead = (notification: NotificationModel): void => {
    router.patch(
      `/notifications/${notification.id}`,
      {},
      {
        preserveScroll: true,
        only: ['notifications'],
      },
    )
  }

  const remove = (notification: NotificationModel): void => {
    router.delete(`/notifications/${notification.id}`, {
      preserveScroll: true,
      only: ['notifications'],
    })
  }

  const markAllAsRead = (): void => {
    router.post(
      MarkAllNotificationsReadController.url(),
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
