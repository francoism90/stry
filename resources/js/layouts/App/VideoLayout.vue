<script setup lang="ts">
import { edit, show } from '@/actions/App/Web/Videos/Controllers/VideoController'
import { index as media } from '@/actions/App/Web/Videos/Controllers/VideoMediaController'
import { index as playlists } from '@/actions/App/Web/Videos/Controllers/VideoPlaylistController'
import { index as transcodes } from '@/actions/App/Web/Videos/Controllers/VideoTranscodeController'
import AppHeader from '@/components/Ui/AppHeader.vue'
import type { Video } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { NavigationMenuItem } from '@nuxt/ui'
import { computed } from 'vue'

const props = defineProps<{
  video: Video
}>()

const links: NavigationMenuItem[] = [
  {
    label: 'View video',
    icon: 'i-lucide-eye',
    to: show.url(props.video.id),
  },
]

const tabs: NavigationMenuItem[] = [
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
]

const meta = computed(() => [props.video.timestamp, props.video.filesize, props.video.user?.name].filter(Boolean))

useEcho<Video>(`videos.${props.video.id}`, '.video.updated', () => router.reload({ only: ['video'] }))
</script>

<template>
  <Head :title="video.title" />

  <UDashboardPanel
    id="video"
    :ui="{ body: 'mx-auto w-full max-w-6xl px-4 sm:px-6' }"
  >
    <template #header>
      <AppHeader />
    </template>

    <template #body>
      <UPage>
        <UPageHeader
          :title="video.title"
          :links="links"
        >
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

        <UNavigationMenu
          :items="tabs"
          variant="link"
          highlight
          :ui="{
            root: 'border-default mt-2 w-full flex-1 border-b',
          }"
        />

        <slot />
      </UPage>
    </template>
  </UDashboardPanel>
</template>
