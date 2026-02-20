<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Playlists/Controllers/PlaylistController'
import type { Playlist } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { NavigationMenuItem } from '@nuxt/ui'

const props = defineProps<{
  playlist: Playlist
}>()

const links: NavigationMenuItem[][] = [
  [
    {
      label: 'General',
      icon: 'i-lucide-film',
      to: edit.url(props.playlist.id),
      exact: true,
    },
  ],
  [
    {
      label: 'View Playlist',
      icon: 'i-lucide-eye',
      to: props.playlist.asset,
      disabled: !props.playlist.valid,
    },
  ],
]

useEcho<Playlist>(`playlists.${props.playlist.id}`, '.playlist.updated', () => router.reload({ only: ['playlist'] }))
</script>

<template>
  <Head :title="playlist.id" />

  <UDashboardPanel id="playlist-resource">
    <template #header>
      <UDashboardNavbar :title="playlist.id">
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
