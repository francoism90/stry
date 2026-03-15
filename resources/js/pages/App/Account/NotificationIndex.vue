<script setup lang="ts">
import { useNotifications } from '@/composables/notifications'
import AccountLayout from '@/layouts/App/AccountLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { NotificationCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import { toRef } from 'vue'

const props = defineProps<{
  notifications: NotificationCollection
}>()

const { hasUnread, getTitle, getMessage, toggleRead, remove, markAllAsRead } = useNotifications(
  toRef(props, 'notifications'),
)

defineOptions({ layout: [DefaultLayout, AccountLayout] })
</script>

<template>
  <Head title="Notifications" />

  <UPageHeader
    title="Notifications"
    description="Your recent activity and alerts."
  >
    <template #links>
      <UButton
        v-if="hasUnread"
        label="Mark all read"
        variant="soft"
        color="neutral"
        icon="i-lucide-check-check"
        @click="markAllAsRead"
      />
    </template>
  </UPageHeader>

  <UPageBody>
    <InfiniteScroll
      data="notifications"
      :buffer="200"
    >
      <div
        v-if="!notifications?.data?.length"
        class="flex flex-col items-center justify-center gap-3 py-24 text-center"
      >
        <UIcon
          name="i-lucide-bell-off"
          class="text-muted size-10"
        />
        <p class="font-semibold">No notifications</p>
        <p class="text-muted text-sm">You're all caught up!</p>
      </div>

      <div
        v-else
        class="flex flex-col"
      >
        <div
          v-for="notification in notifications?.data"
          :key="notification.id"
          class="border-default flex items-start gap-4 border-b py-4 last:border-0"
          :class="{ 'opacity-60': notification.read_at }"
        >
          <div
            class="mt-1 shrink-0 rounded-full p-2"
            :class="notification.read_at ? 'bg-neutral-100 dark:bg-neutral-800' : 'bg-primary-50 dark:bg-primary-950'"
          >
            <UIcon
              name="i-lucide-bell"
              class="size-4"
              :class="notification.read_at ? 'text-muted' : 'text-primary'"
            />
          </div>

          <div class="min-w-0 flex-1">
            <p class="text-sm font-medium">{{ getTitle(notification) }}</p>
            <p
              v-if="getMessage(notification)"
              class="text-muted mt-0.5 text-sm"
            >
              {{ getMessage(notification) }}
            </p>
            <p class="text-muted mt-1 text-xs">{{ notification.created_at }}</p>
          </div>

          <div class="flex shrink-0 items-center gap-1">
            <UButton
              :icon="notification.read_at ? 'i-lucide-mail' : 'i-lucide-mail-open'"
              variant="ghost"
              color="neutral"
              size="xs"
              @click="toggleRead(notification)"
            />
            <UButton
              icon="i-lucide-trash-2"
              variant="ghost"
              color="neutral"
              size="xs"
              @click="remove(notification)"
            />
          </div>
        </div>
      </div>
    </InfiniteScroll>
  </UPageBody>
</template>
