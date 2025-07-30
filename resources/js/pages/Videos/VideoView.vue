<script setup lang="ts">
import VideoController from '@/actions/App/Web/Videos/Controllers/VideoController'
import Page from '@/components/Ui/Page.vue'
import PageBody from '@/components/Ui/PageBody.vue'
import PageFeature from '@/components/Ui/PageFeature.vue'
import VideoCarousel from '@/components/Video/VideoCarousel.vue'
import VideoPlayer from '@/components/Video/VideoPlayer.vue'
import type { Playlist, Video } from '@/types'
import { Deferred, Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { NavigationMenuItem } from '@nuxt/ui'
import { computed, ref } from 'vue'

interface Props {
  video: Video
  playlist: Playlist | null
  time?: number | null
  queue?: Video[] | null
}

const props = defineProps<Props>()

const items = ref<NavigationMenuItem[][]>([
  [
    {
      label: '0',
      icon: 'i-lucide-thumbs-up',
      to: '/search',
    },
    {
      label: 'Edit',
      icon: 'i-lucide-clipboard-pen',
      to: VideoController.edit.url(props.video.id),
    },
    {
      label: 'Save',
      icon: 'i-lucide-bookmark',
      to: '/lists',
    },
  ],
])

const src = computed(() => (props.playlist?.valid ? props.playlist.asset : ''))
// const time = computed(() => props.time ?? 0)

useEcho<Video>(`videos.${props.video.id}`, '.video.updated', () => router.reload({ only: ['video'] }))
useEcho<Video>(`videos.${props.video.id}`, '.playlist.created', () => router.reload({ only: ['playlist'] }))
</script>

<template>
  <Head :title="video.name" />

  <Page>
    <PageBody>
      <VideoPlayer :src />

      <div class="flex flex-col gap-2 py-4">
        <PageFeature :title="video.name" />

        <UNavigationMenu
          orientation="horizontal"
          :items="items"
          :ui="{
            root: 'size-full items-center overflow-x-auto',
            list: 'inline-flex size-full items-center gap-2',
            link: 'rounded-full bg-neutral-800/40',
            linkLeadingIcon: 'size-3.5',
            linkLabel: 'text-xs text-neutral-400',
          }"
        />
      </div>

      <Deferred :data="['queue']">
        <template #fallback>
          <div class="sr-only">Loading sections...</div>
        </template>

        <VideoCarousel
          label="Up Next"
          :items="queue"
          :actions="[{ label: 'Show All', href: '/', trailingIcon: 'i-lucide-chevron-right' }]"
        />
      </Deferred>
    </PageBody>
  </Page>
</template>
