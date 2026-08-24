<script setup lang="ts">
import { useAuth } from '@/composables/auth'
import { router, usePage } from '@inertiajs/vue3'
import { useEchoNotification } from '@laravel/echo-vue'
import { computed } from 'vue'

const { user: auth } = useAuth()

const unreadCount = computed(() => usePage().props.unread)

if (auth.value) {
  useEchoNotification(`users.${auth.value.id}`, () => router.reload({ only: ['unread'] }))
}
</script>

<template>
  <UChip
    :show="unreadCount > 0"
    :text="unreadCount > 99 ? '99+' : unreadCount"
    color="error"
    :ui="{ base: 'h-4 min-w-4 px-1 text-[10px] leading-none' }"
  >
    <UButton
      variant="link"
      color="neutral"
      icon="i-lucide-bell"
      to="/notifications"
    />
  </UChip>
</template>
