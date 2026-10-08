<script setup lang="ts">
import { useNotifications } from '@/composables/notifications'
import type { Notification } from '@/types'
import { useTimeAgo } from '@vueuse/core'
import { computed } from 'vue'

const props = defineProps<{
  item: Notification
}>()

const { getTitle, getMessage, getUrl, getThumb, getIcon, getIconColor, toggleRead, remove } = useNotifications()

const isUnread = computed(() => !props.item.read_at)

const createdAgo = useTimeAgo(() => new Date(props.item.created_at.replace(' ', 'T')))
</script>

<template>
  <li
    class="group/row relative flex items-center gap-3 rounded-lg border p-3 transition-colors"
    :class="
      isUnread
        ? 'border-(--glass-border) bg-(--glass) backdrop-blur-md backdrop-saturate-140'
        : 'border-transparent hover:bg-(--glass)'
    "
  >
    <span
      class="grid size-9 shrink-0 place-items-center rounded-full"
      :class="[isUnread ? 'bg-(--glass-strong)' : 'bg-(--glass)', getIconColor(item)]"
      aria-hidden="true"
    >
      <UIcon
        :name="getIcon(item)"
        class="size-4.5"
      />
    </span>

    <span class="flex min-w-0 flex-1 flex-col gap-0.5 text-sm">
      <span class="flex flex-wrap items-center gap-2">
        <ULink
          v-if="getUrl(item)"
          :to="getUrl(item)"
          class="font-medium after:absolute after:inset-0"
          :class="isUnread ? 'text-highlighted' : 'text-default'"
        >
          {{ getTitle(item) }}
        </ULink>

        <span
          v-else
          class="font-medium"
          :class="isUnread ? 'text-highlighted' : 'text-default'"
        >
          {{ getTitle(item) }}
        </span>

        <span
          v-if="isUnread"
          class="flex items-center gap-1 text-xs font-medium text-primary"
        >
          <span
            class="size-1.5 rounded-full bg-primary"
            aria-hidden="true"
          />
          New
        </span>
      </span>

      <span
        v-if="getMessage(item)"
        class="text-muted"
      >
        {{ getMessage(item) }}
      </span>

      <time
        :datetime="item.created_at"
        class="text-xs text-muted"
      >
        {{ createdAgo }}
      </time>
    </span>

    <span
      v-if="getThumb(item)"
      class="hidden aspect-video w-24 shrink-0 overflow-hidden rounded-lg bg-muted sm:block"
    >
      <img
        :src="getThumb(item)"
        alt=""
        class="size-full object-cover"
        loading="lazy"
        decoding="async"
      />
    </span>

    <span
      class="relative z-10 flex shrink-0 flex-col gap-1 opacity-100 transition-opacity sm:flex-row sm:opacity-0 sm:group-focus-within/row:opacity-100 sm:group-hover/row:opacity-100"
    >
      <UButton
        :icon="isUnread ? 'i-lucide-mail-open' : 'i-lucide-mail'"
        :aria-label="isUnread ? 'Mark as read' : 'Mark as unread'"
        color="neutral"
        variant="ghost"
        size="xs"
        class="rounded-full"
        @click="toggleRead(item)"
      />

      <UButton
        icon="i-lucide-trash-2"
        aria-label="Delete notification"
        color="neutral"
        variant="ghost"
        size="xs"
        class="rounded-full"
        @click="remove(item)"
      />
    </span>
  </li>
</template>
