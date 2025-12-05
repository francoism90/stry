<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Videos/Controllers/VideoController'
import TagController from '@/actions/App/Client/Tags/Controllers/TagController'
import VideoList from '@/components/Videos/VideoList.vue'
import VideoPlayer from '@/components/Videos/VideoPlayer.vue'
import type { Video, VideoCollection } from '@/types'
import { Deferred, Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { ButtonProps } from '@nuxt/ui'
import { ref } from 'vue'

const props = defineProps<{
  video: Video
  queue?: VideoCollection
}>()

const links = ref<ButtonProps[]>([
  {
    label: 'Edit',
    to: edit.url(props.video.id),
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
    <UPageBody class="mt-4 space-y-4 px-4 sm:px-6">
      <VideoPlayer />

      <UPageFeature :title="video.title">
        <template #description>
          <p v-html="video.description" />

          <div class="flex items-center gap-2 overflow-auto">
            <UButton
              v-for="(tag, index) in video.tags"
              :key="index"
              :label="tag.name"
              :to="TagController.url(tag.id)"
              variant="outline"
              size="sm"
              class="mt-2"
            />
          </div>
        </template>
      </UPageFeature>

      <div class="flex items-center gap-2 overflow-auto">
        <UButton
          v-for="(link, index) in links"
          :key="index"
          v-bind="link"
          variant="soft"
          size="sm"
          class="mb-2"
        />
      </div>

      <Deferred data="queue">
        <template #fallback>
          <div class="sr-only">Loading queue...</div>
        </template>

        <UPageFeature title="Up next" />
        <VideoList :items="queue" />
      </Deferred>
    </UPageBody>
  </UPage>
</template>
