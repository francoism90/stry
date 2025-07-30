<script setup lang="ts">
import Page from '@/components/Ui/Page.vue'
import PageBody from '@/components/Ui/PageBody.vue'
import PageFeature from '@/components/Ui/PageFeature.vue'
import VideoNavigation from '@/components/Video/VideoNavigation.vue'
import VideoPlayer from '@/components/Video/VideoPlayer.vue'
import VideoSection from '@/components/Video/VideoSection.vue'
import type { Playlist, Video } from '@/types'
import { Deferred, Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import { computed } from 'vue'

interface Props {
  item: Video
  playlist: Playlist | null
  time?: number | null
  queue?: Video[] | null
}

const props = defineProps<Props>()

const src = computed(() => (props.playlist?.valid ? props.playlist.asset : ''))
// const time = computed(() => props.time ?? 0)

useEcho<Video>(`videos.${props.item.id}`, '.video.updated', () => router.reload({ only: ['item'] }))
useEcho<Video>(`videos.${props.item.id}`, '.playlist.created', () => router.reload({ only: ['playlist'] }))
</script>

<template>
  <Head :title="item.name" />

  <Page>
    <PageBody>
      <VideoPlayer :src />

      <PageFeature
        :title="item.name"
        :description="item.summary"
      />

      <VideoNavigation :item />

      {{ playlist }}

      <Deferred :data="['queue']">
        <template #fallback>
          <div class="sr-only">Loading sections...</div>
        </template>

        <VideoSection
          label="Up Next"
          :actions="[{ label: 'Show All', href: '/videos', trailingIcon: 'i-lucide-chevron-right' }]"
          :items="queue"
        />
      </Deferred>
    </PageBody>
  </Page>
</template>
