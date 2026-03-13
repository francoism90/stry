<script setup lang="ts">
import AppLogo from '@/components/Ui/AppLogo.vue'
import { useAuth } from '@/composables/auth'
import { useNotifications } from '@/composables/notifications'
import { router } from '@inertiajs/vue3'
import type { DropdownMenuItem, NavigationMenuItem } from '@nuxt/ui'
import { computed } from 'vue'

useNotifications()
const { user } = useAuth()

const navItems: NavigationMenuItem[] = [
  {
    label: 'Videos',
    to: '/',
    exact: true,
  },
  {
    label: 'Collections',
    to: '/collections',
  },
  {
    label: 'Tags',
    to: '/tags',
  },
]

const menuItems = computed<DropdownMenuItem[][]>(() => [
  [
    {
      label: 'Profile',
      icon: 'i-lucide-user',
      to: '/profile',
    },
    {
      label: 'Settings',
      icon: 'i-lucide-settings',
      to: '/settings',
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
  <UHeader
    :ui="{
      root: 'border-(--ui-border) bg-neutral-900/80 backdrop-blur-md backdrop-saturate-150',
      left: 'gap-4 *:inline-flex *:items-center',
    }"
  >
    <template #left>
      <AppLogo />

      <UNavigationMenu
        :items="navItems"
        variant="link"
      />
    </template>

    <template #right>
      <UButton
        variant="ghost"
        color="neutral"
        icon="i-lucide-bell"
      />

      <UDropdownMenu
        :items="menuItems"
        :content="{ align: 'end', collisionPadding: 12 }"
      >
        <UAvatar
          :src="user?.avatar ?? undefined"
          :alt="user?.name ?? 'User'"
          size="sm"
          class="cursor-pointer"
        />
      </UDropdownMenu>
    </template>
  </UHeader>
</template>
