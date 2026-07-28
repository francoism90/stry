<script setup lang="ts">
import AppLogo from '@/components/Ui/AppLogo.vue'
import { useAuth } from '@/composables/auth'
import { router } from '@inertiajs/vue3'
import type { DropdownMenuItem, NavigationMenuItem } from '@nuxt/ui'
import { computed } from 'vue'

const { user } = useAuth()

const navItems: NavigationMenuItem[] = [
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
  <UHeader
    :toggle="false"
    :ui="{
      root: 'w-full bg-neutral-900/80 backdrop-blur-md backdrop-saturate-150',
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
        icon="i-lucide-search"
        to="/search"
      />

      <UButton
        variant="ghost"
        color="neutral"
        icon="i-lucide-bell"
        to="/notifications"
      />

      <UDropdownMenu
        :items="menuItems"
        :content="{ align: 'end', collisionPadding: 12 }"
      >
        <UAvatar
          :src="user?.avatar ?? undefined"
          :alt="user?.name ?? 'User'"
          :ui="{ root: 'cursor-pointer' }"
          size="sm"
        />
      </UDropdownMenu>
    </template>
  </UHeader>
</template>
