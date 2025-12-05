<script setup lang="ts">
import VideoPlayer from '@/components/Videos/VideoPlayer.vue'
import type { Video, VideoCollection } from '@/types'
import { Deferred, Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { ButtonProps } from '@nuxt/ui'
import { ref } from 'vue'

interface Props {
  video: Video
  queue?: VideoCollection
}

const props = defineProps<Props>()

const links = ref<ButtonProps[]>([
  {
    label: 'Edit',
    icon: 'i-lucide-clipboard-pen',
  },
])

useEcho<Video>(`videos.${props.video.id}`, '.video.updated', () => router.reload({ only: ['video'] }))
useEcho<Video>(`videos.${props.video.id}`, '.playlist.created', () => router.reload({ only: ['playlist'] }))
useEcho<Video>(`videos.${props.video.id}`, '.playlist.updated', () => router.reload({ only: ['playlist'] }))
</script>

<template>
  <Head :title="video.title" />

  <UPage>
    <UPageBody>
      <VideoPlayer />

      <UPageHeader
        :title="video.title"
        :links="links"
      >
        <template #description>
          <div v-html="video.description" />
          <TagItems :items="video.tags" />
        </template>
      </UPageHeader>

      <Deferred data="queue">
        <template #fallback>
          <div class="sr-only">Loading queue...</div>
        </template>

        <UPageFeature
          class="py-4"
          title="Up next"
        />

        <VideoItems
          orientation="horizontal"
          :items="queue"
        />
      </Deferred>
    </UPageBody>
  </UPage>
</template>
