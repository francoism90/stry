import MarkAllNotificationsReadController from '@/actions/App/Web/Notifications/Controllers/MarkAllNotificationsReadController'
import type { NotificationCollection, Notification as NotificationModel } from '@/types'
import { router } from '@inertiajs/vue3'
import { computed, type Ref } from 'vue'

export function useNotifications(notifications?: Ref<NotificationCollection>) {
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

  return { hasUnread, getTitle, getMessage, toggleRead, remove, markAllAsRead }
}
