<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Videos/Controllers/VideoController'
import { index as media } from '@/actions/App/Admin/Videos/Controllers/VideoMediaController'
import { index as playlists } from '@/actions/App/Admin/Videos/Controllers/VideoPlaylistController'
import VideoController from '@/actions/App/Client/Videos/Controllers/VideoController'
import type { Video } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'

const props = defineProps<{
  video: Video
}>()

const links: NavigationMenuItem[][] = [
  [
    {
      label: 'General',
      icon: 'i-lucide-film',
      to: edit.url(props.video.id),
      exact: true,
    },
    {
      label: 'Media',
      icon: 'i-lucide-images',
      to: media.url(props.video.id),
    },
    {
      label: 'Playlists',
      icon: 'i-lucide-list-video',
      to: playlists.url(props.video.id),
    },
    {
      label: 'Metadata',
      icon: 'i-lucide-file-braces',
    },
  ],
  [
    {
      label: 'View Video',
      icon: 'i-lucide-eye',
      to: VideoController.url(props.video.id),
    },
  ],
]
</script>

<template>
  <Head :title="video.title" />

  <UDashboardPanel id="video">
    <template #header>
      <UDashboardNavbar title="Video">
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
