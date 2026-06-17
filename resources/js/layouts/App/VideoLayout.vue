<script setup lang="ts">
import { edit, show } from '@/actions/App/Web/Videos/Controllers/VideoController'
import { index as media } from '@/actions/App/Web/Videos/Controllers/VideoMediaController'
import { index as playlists } from '@/actions/App/Web/Videos/Controllers/VideoPlaylistController'
import { index as transcodes } from '@/actions/App/Web/Videos/Controllers/VideoTranscodeController'
import AppHeader from '@/components/Ui/AppHeader.vue'
import { useEcho } from '@/composables/echo'
import { index } from '@/routes/videos'
import type { Video } from '@/types'
import { Head, router, usePage } from '@inertiajs/vue3'
import type { NavigationMenuItem, SelectItem } from '@nuxt/ui'
import { computed } from 'vue'

const props = defineProps<{
  video: Video
  locales?: SelectItem[]
}>()

const page = usePage()
const { listen } = useEcho()

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
const locale = computed(() => page.props.locale)

listen<Video>(`videos.${props.video.id}`, '.video.updated', () => router.reload({ only: ['video'] }))
listen<Video>(`videos.${props.video.id}`, '.video.trashed', () => router.visit(index.url()))
</script>

<template>
  <Head :title="video.title" />

  <UDashboardPanel id="video">
    <template #header>
      <AppHeader />
    </template>

    <template #body>
      <UPage class="mx-auto w-full max-w-6xl px-4 sm:px-6">
        <UPageHeader :title="video.title">
          <template #description>
            <div class="dot-separated flex flex-wrap items-center text-sm text-muted">
              <span
                v-for="(item, index) in meta"
                :key="index"
              >
                {{ item }}
              </span>
            </div>
          </template>

          <template #links>
            <USelect
              v-if="props.locales"
              :model-value="locale"
              :items="props.locales"
              color="neutral"
              size="sm"
              variant="outline"
              class="w-28"
            />

            <UButton
              v-for="link in links"
              :key="link.label"
              :label="link.label"
              :icon="link.icon as string"
              :to="link.to as string"
              color="neutral"
              variant="outline"
              size="sm"
            />
          </template>
        </UPageHeader>

        <UNavigationMenu
          :items="tabs"
          variant="link"
          highlight
          :ui="{
            root: 'w-full flex-1 border-b border-default',
          }"
        />

        <slot />
      </UPage>
    </template>
  </UDashboardPanel>
</template>
