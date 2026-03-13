<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Videos/Controllers/VideoController'
import { index as media } from '@/actions/App/Admin/Videos/Controllers/VideoMediaController'
import { index as playlists } from '@/actions/App/Admin/Videos/Controllers/VideoPlaylistController'
import { index as transcodes } from '@/actions/App/Admin/Videos/Controllers/VideoTranscodeController'
import VideoController from '@/actions/App/Client/Videos/Controllers/VideoController'
import type { Video } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { NavigationMenuItem } from '@nuxt/ui'
import { computed } from 'vue'

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
      label: 'Transcodes',
      icon: 'i-lucide-cpu',
      to: transcodes.url(props.video.id),
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

const meta = computed(() => [props.video.timestamp, props.video.filesize, props.video.user?.name].filter(Boolean))

useEcho<Video>(`videos.${props.video.id}`, '.video.updated', () => router.reload({ only: ['video'] }))
</script>

<template>
  <Head :title="video.title" />

  <UDashboardPanel id="video-resource">
    <template #header>
      <UDashboardNavbar :title="video.title">
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
      <UPage>
        <UPageHeader :title="video.title">
          <template #description>
            <div class="dot-separated text-muted flex flex-wrap items-center text-sm">
              <span
                v-for="(item, index) in meta"
                :key="index"
              >
                {{ item }}
              </span>
            </div>
          </template>
        </UPageHeader>

        <slot />
      </UPage>
    </template>
  </UDashboardPanel>
</template>
