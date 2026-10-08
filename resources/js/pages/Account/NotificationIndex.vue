<script setup lang="ts">
import { index } from '@/actions/Modules/Web/Account/Controllers/NotificationsController'
import NotificationItem from '@/components/Notifications/NotificationItem.vue'
import { useNotifications } from '@/composables/notifications'
import ResourceLayout from '@/layouts/App/ResourceLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { Notification, NotificationCollection } from '@/types'
import { Head, InfiniteScroll, router, setLayoutProps, usePage } from '@inertiajs/vue3'
import { computed, toRef } from 'vue'

const props = defineProps<{
  notifications: NotificationCollection
  filter: 'all' | 'unread'
}>()

defineOptions({
  layout: [AppLayout, ResourceLayout],
})

// Layouts also receive page props; keep this page's read filter out of the layout's query filter.
setLayoutProps({
  filter: undefined,
})

const { hasUnread, markAllAsRead } = useNotifications(toRef(props, 'notifications'))

const unreadCount = computed<number>(() => usePage().props.unread ?? 0)

const filters = [
  { label: 'All', value: 'all' },
  { label: 'Unread', value: 'unread' },
] as const

const applyFilter = (filter: 'all' | 'unread'): void => {
  router.get(
    index.url({ query: { filter: filter === 'all' ? undefined : filter } }),
    {},
    {
      preserveState: true,
      preserveScroll: true,
      only: ['notifications', 'filter'],
      reset: ['notifications'],
    },
  )
}

const isToday = (notification: Notification): boolean =>
  new Date(notification.created_at.replace(' ', 'T')).toDateString() === new Date().toDateString()

const sections = computed(() =>
  [
    { id: 'today', title: 'Today', items: (props.notifications?.data ?? []).filter(isToday) },
    {
      id: 'earlier',
      title: 'Earlier',
      items: (props.notifications?.data ?? []).filter((notification) => !isToday(notification)),
    },
  ].filter((section) => section.items.length),
)
</script>

<template>
  <Head title="Notifications" />

  <UPage>
    <div class="flex flex-col gap-6 pt-4">
      <header class="flex flex-col gap-1">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <h1 class="min-w-0 flex-[1_1_15rem] text-3xl font-bold text-highlighted sm:text-4xl">Notifications</h1>

          <UButton
            v-if="hasUnread"
            label="Mark all read"
            icon="i-lucide-check-check"
            color="neutral"
            variant="outline"
            size="sm"
            class="rounded-full bg-(--glass) text-highlighted ring-(--glass-border) backdrop-blur-md backdrop-saturate-140 hover:bg-(--glass-strong)"
            @click="markAllAsRead"
          />
        </div>

        <p class="text-sm text-muted">
          {{ unreadCount > 0 ? `${Intl.NumberFormat().format(unreadCount)} unread` : "You're all caught up" }}
        </p>
      </header>

      <div
        class="flex flex-wrap gap-2"
        role="group"
        aria-label="Show"
      >
        <UButton
          v-for="option in filters"
          :key="option.value"
          :label="option.label"
          :aria-pressed="filter === option.value"
          color="neutral"
          variant="outline"
          size="sm"
          class="rounded-lg px-3"
          :class="
            filter === option.value
              ? 'bg-white text-black ring-white hover:bg-white'
              : 'bg-(--glass) text-highlighted ring-(--glass-border) backdrop-blur-md backdrop-saturate-140 hover:bg-(--glass-strong)'
          "
          @click="applyFilter(option.value)"
        />
      </div>

      <InfiniteScroll
        data="notifications"
        items-element="#notification-sections"
        :buffer="200"
      >
        <div
          id="notification-sections"
          class="flex flex-col gap-6"
        >
          <UEmpty
            v-if="!sections.length"
            icon="i-lucide-bell-off"
            :title="filter === 'unread' ? 'No unread notifications' : 'No notifications'"
            description="You're all caught up!"
            variant="naked"
            class="py-24"
          />

          <section
            v-for="section in sections"
            :key="section.id"
            :aria-labelledby="`notifications-${section.id}`"
            class="flex flex-col gap-2"
          >
            <h2
              :id="`notifications-${section.id}`"
              class="text-xs font-medium text-muted"
            >
              {{ section.title }}
            </h2>

            <ul class="flex flex-col gap-1">
              <NotificationItem
                v-for="notification in section.items"
                :key="notification.id"
                :item="notification"
              />
            </ul>
          </section>
        </div>
      </InfiniteScroll>
    </div>
  </UPage>
</template>
