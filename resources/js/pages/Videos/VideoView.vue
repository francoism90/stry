<script setup lang="ts">
import { edit } from '@/actions/App/Web/Videos/Controllers/VideoController'
import PageBadge from '@/components/Ui/PageBadge.vue'
import PageBody from '@/components/Ui/PageBody.vue'
import PageColumns from '@/components/Ui/PageColumns.vue'
import PageDetails from '@/components/Ui/PageDetails.vue'
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageSection from '@/components/Ui/PageSection.vue'
import VideoCarousel from '@/components/Video/VideoCarousel.vue'
import VideoPlayer from '@/components/Video/VideoPlayer.vue'
import type { DetailListItem, Video } from '@/types'
import { Deferred, Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { NavigationMenuItem } from '@nuxt/ui'
import { useDateFormat } from '@vueuse/core'
import { ref } from 'vue'

interface Props {
  video: Video
  queue?: Video[] | null
}

const props = defineProps<Props>()

const items = ref<NavigationMenuItem[]>([
  { label: '0', icon: 'i-lucide-thumbs-up', to: '/search' },
  { label: 'Edit', icon: 'i-lucide-clipboard-pen', to: edit.url(props.video.id) },
  { label: 'Save', icon: 'i-lucide-bookmark', to: '/lists' },
])

const details = ref<DetailListItem[]>([
  { label: 'Created', value: useDateFormat(props.video.created_at, 'YYYY-MM-DD') },
  { label: 'Duration', value: props.video.timestamp },
])

useEcho<Video>(`videos.${props.video.id}`, '.video.updated', () => router.reload({ only: ['video'] }))
useEcho<Video>(`videos.${props.video.id}`, '.playlist.created', () => router.reload({ only: ['playlist'] }))
useEcho<Video>(`videos.${props.video.id}`, '.playlist.updated', () => router.reload({ only: ['playlist'] }))
</script>

<template>
  <Head :title="video.name" />

  <PageBody>
    <VideoPlayer />

    <PageSection class="gap-4 py-2">
      <PageColumns>
        <template #left>
          <PageFeature :title="video.name" />
          <PageDetails :items="details" />
          <PageBadge :items="video.tags" />
        </template>

        <template #right>
          <UNavigationMenu
            :items="items"
            :ui="{
              root: 'size-full items-center overflow-x-auto',
              list: 'inline-flex size-full items-center gap-2',
              link: 'rounded-full bg-neutral-800/40',
              linkLeadingIcon: 'size-3.5',
              linkLabel: 'text-xs text-neutral-400',
            }"
          />
        </template>
      </PageColumns>

      <Deferred :data="['queue']">
        <template #fallback>
          <div class="sr-only">Loading sections...</div>
        </template>

        <VideoCarousel
          label="Up Next"
          :items="queue"
          :actions="[{ label: 'Show All', href: '/videos', trailingIcon: 'i-lucide-chevron-right' }]"
        />
      </Deferred>
    </PageSection>
  </PageBody>
</template>
