<script setup lang="ts">
import TagItems from '@/components/Tag/TagItems.vue'
import VideoItems from '@/components/Video/VideoItems.vue'
import VideoPlayer from '@/components/Video/VideoPlayer.vue'
import VideoSaveModal from '@/components/Video/VideoSaveModal.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { Video, VideoCollection } from '@/types'
import { Deferred, Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { ButtonProps } from '@nuxt/ui'
import { ref } from 'vue'

interface Props {
  video: Video
  queue?: VideoCollection
}

defineOptions({ layout: DashboardLayout })

const props = defineProps<Props>()

const links = ref<ButtonProps[]>([
  {
    label: 'Edit',
    icon: 'i-lucide-clipboard-pen',
    to: edit.url(props.video.id),
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
      <UContainer class="pt-2">
        <VideoPlayer />
      </UContainer>

      <UContainer>
        <UPageHeader :title="video.title">
          <template #description>
            <div v-html="video.description" />
            <TagItems :items="video.tags" />
          </template>

          <template #links>
            <VideoSaveModal :item="video" />
          </template>
        </UPageHeader>
      </UContainer>

      <UContainer>
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
      </UContainer>
    </UPageBody>
  </UPage>
</template>
