import MarkAllNotificationsReadController from '@/actions/Modules/Web/Notifications/Controllers/MarkAllNotificationsReadController'
import type { NotificationCollection, Notification as NotificationModel } from '@/types'
import { router } from '@inertiajs/vue3'
import { computed, type Ref } from 'vue'

type NotificationLevel = 'success' | 'error' | 'warning' | 'info'

const levelIcons: Record<NotificationLevel, string> = {
  success: 'i-lucide-circle-check',
  error: 'i-lucide-circle-x',
  warning: 'i-lucide-flag',
  info: 'i-lucide-bell',
}

const levelColors: Record<NotificationLevel, string> = {
  success: 'text-success',
  error: 'text-error',
  warning: 'text-warning',
  info: 'text-primary',
}

export function useNotifications(notifications?: Ref<NotificationCollection>) {
  const hasUnread = computed(() => notifications?.value?.data?.some((n: NotificationModel) => !n.read_at) ?? false)

  const getTitle = (notification: NotificationModel): string =>
    (notification.data.title as string | undefined) ?? notification.type

  const getMessage = (notification: NotificationModel): string | undefined =>
    notification.data.message as string | undefined

  const getUrl = (notification: NotificationModel): string | undefined => notification.data.url as string | undefined

  const getThumb = (notification: NotificationModel): string | undefined =>
    notification.data.thumb as string | undefined

  const getLevel = (notification: NotificationModel): NotificationLevel => {
    const level = notification.data.level as string | undefined

    return level && level in levelIcons ? (level as NotificationLevel) : 'info'
  }

  const getIcon = (notification: NotificationModel): string => levelIcons[getLevel(notification)]

  const getIconColor = (notification: NotificationModel): string => levelColors[getLevel(notification)]

  const toggleRead = (notification: NotificationModel): void => {
    router.patch(
      `/notifications/${notification.id}`,
      {},
      {
        preserveScroll: true,
        only: ['notifications', 'unread'],
        reset: ['notifications'],
      },
    )
  }

  const remove = (notification: NotificationModel): void => {
    router.delete(`/notifications/${notification.id}`, {
      preserveScroll: true,
      only: ['notifications', 'unread'],
      reset: ['notifications'],
    })
  }

  const markAllAsRead = (): void => {
    router.post(
      MarkAllNotificationsReadController.url(),
      {},
      {
        preserveScroll: true,
        only: ['notifications', 'unread'],
        reset: ['notifications'],
      },
    )
  }

  return {
    hasUnread,
    getTitle,
    getMessage,
    getUrl,
    getThumb,
    getIcon,
    getIconColor,
    toggleRead,
    remove,
    markAllAsRead,
  }
}
