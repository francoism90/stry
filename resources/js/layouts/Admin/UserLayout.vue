<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Users/Controllers/UserController'
import type { User } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'

const props = defineProps<{
  user: User
}>()

const links: NavigationMenuItem[][] = [
  [
    {
      label: 'General',
      icon: 'i-lucide-user',
      to: edit.url(props.user.id),
      exact: true,
    },
    {
      label: 'Roles & Permissions',
      icon: 'i-lucide-shield',
    },
    {
      label: 'Activity',
      icon: 'i-lucide-activity',
    },
  ],
]
</script>

<template>
  <Head :title="user.name" />

  <UDashboardPanel id="user">
    <template #header>
      <UDashboardNavbar :title="user.name">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>
      </UDashboardNavbar>

      <UDashboardToolbar>
        <UNavigationMenu
          :items="links"
          highlight
          class="-mx-1 flex-1"
        />
      </UDashboardToolbar>
    </template>

    <template #body>
      <slot />
    </template>
  </UDashboardPanel>
</template>
