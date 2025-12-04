<script setup lang="ts">
import DashboardTenantMenu from '@/components/Admin/DashboardTenantMenu.vue'
import DashboardUserMenu from '@/components/Admin/DashboardUserMenu.vue'
import type { NavigationMenuItem } from '@nuxt/ui'

const links: NavigationMenuItem[][] = [
  [
    {
      label: 'Home',
      icon: 'i-lucide-house',
      to: '/admin',
      exact: true,
    },
    {
      label: 'Inbox',
      icon: 'i-lucide-inbox',
      to: '/admin/inbox',
      badge: '4',
    },
  ],
  [
    {
      label: 'Videos',
      icon: 'i-lucide-videotape',
      to: '/admin/videos',
    },
    {
      label: 'Tags',
      icon: 'i-lucide-tags',
      to: '/admin/tags',
    },
    {
      label: 'Media',
      icon: 'i-lucide-files',
      to: '/admin/media',
    },
    {
      label: 'Playlists',
      icon: 'i-lucide-image-play',
      to: '/admin/playlists',
    },
    {
      label: 'Users',
      icon: 'i-lucide-users',
      to: '/admin/users',
    },
  ],
  [
    {
      label: 'Settings',
      icon: 'i-lucide-settings',
      children: [
        {
          label: 'Horizon',
          to: '/horizon',
          target: '_blank',
        },
        {
          label: 'Telescope',
          to: '/telescope',
          target: '_blank',
        },
      ],
    },
  ],
]
</script>

<template>
  <UDashboardSidebar
    id="default"
    collapsible
    :min-size="16"
    :max-size="16"
    :default-size="16"
    :ui="{
      root: 'bg-elevated/25 lg:sticky lg:top-0 lg:max-h-dvh',
      footer: 'lg:border-t lg:border-default',
    }"
  >
    <template #header="{ collapsed }">
      <DashboardTenantMenu :collapsed="collapsed" />
    </template>

    <template #default="{ collapsed }">
      <UDashboardSearchButton
        :collapsed="collapsed"
        class="bg-transparent ring-default"
      />

      <UNavigationMenu
        :collapsed="collapsed"
        :ui="{ root: 'gap-3', link: 'py-2' }"
        :items="links"
        orientation="vertical"
        tooltip
        popover
      />
    </template>

    <template #footer="{ collapsed }">
      <DashboardUserMenu :collapsed="collapsed" />
    </template>
  </UDashboardSidebar>
</template>
