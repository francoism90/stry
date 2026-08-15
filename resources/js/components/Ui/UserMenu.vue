<script setup lang="ts">
import { useAuth } from '@/composables/auth'
import { router } from '@inertiajs/vue3'
import type { DropdownMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

const { user } = useAuth()

const items = ref<DropdownMenuItem[][]>([
  [
    {
      label: 'Account',
      icon: 'i-lucide-user',
      to: '/account',
    },
    {
      label: 'Settings',
      icon: 'i-lucide-settings',
      to: '/settings',
    },
    {
      label: 'Profiles',
      icon: 'i-lucide-users',
      to: '/profiles',
    },
  ],
  [
    {
      label: 'Log out',
      icon: 'i-lucide-log-out',
      onClick: () => router.post('/logout'),
    },
  ],
])
</script>

<template>
  <UDropdownMenu
    arrow
    :items="items"
    :content="{ align: 'end', collisionPadding: 12 }"
  >
    <UAvatar
      :src="user?.avatar ?? undefined"
      :alt="user?.name ?? 'User'"
      :ui="{
        root: 'cursor-pointer p-1',
        fallback: 'flex size-full items-center justify-center',
      }"
      size="sm"
    />
  </UDropdownMenu>
</template>
