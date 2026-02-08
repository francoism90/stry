<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Videos/Controllers/VideoController'
import AppNavbar from '@/components/Ui/AppNavbar.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import VideoPlayer from '@/components/Videos/VideoPlayer.vue'
import VideoTags from '@/components/Videos/VideoTags.vue'
import { useVideo } from '@/composables/video'
import type { Video } from '@/types'
import { Deferred, Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { ButtonProps } from '@nuxt/ui'
import { computed } from 'vue'

const props = defineProps<{
  video: Video
  queue?: Video[]
}>()

const { toggleLike, toggleSave } = useVideo(props.video)
const links = computed<ButtonProps[]>(() => [
  {
    label: 'Edit',
    to: edit.url(props.video.id),
    icon: 'i-lucide-clipboard-pen',
  },
  {
    label: props.video.liked ? 'Unlike' : 'Like',
    icon: props.video.liked ? 'i-lucide-heart' : 'i-lucide-heart-plus',
    onClick: () => toggleLike(),
  },
  {
    label: props.video.saved ? 'Unsave' : 'Save',
    icon: props.video.saved ? 'i-lucide-bookmark' : 'i-lucide-bookmark-plus',
    onClick: () => toggleSave(),
  },
])

useEcho<Video>(`videos.${props.video.id}`, '.video.updated', () => router.reload({ only: ['video'] }))
useEcho<Video>(`videos.${props.video.id}`, '.playlist.created', () => router.reload({ only: ['playlist'] }))
useEcho<Video>(`videos.${props.video.id}`, '.playlist.updated', () => router.reload({ only: ['playlist'] }))
</script>

<template>
  <Head :title="video.title" />

  <UDashboardPanel id="play">
    <template #header>
      <AppNavbar />
    </template>

    <template #body>
      <UPage>
        <VideoPlayer />

        <UPageHeader
          :title="video.title"
          :links="links"
          :ui="{
            title: 'font-serif text-xl sm:text-2xl',
            links: 'flex-nowrap',
            description: 'flex flex-col gap-3 text-base',
          }"
        >
          <template #description>
            <p
              v-if="video.description?.length"
              v-html="video.description"
            />

            <VideoTags :items="video.tags" />
          </template>
        </UPageHeader>

        <UPageBody class="mt-4 space-y-4 pb-8">
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
  </UDashboardPanel>
</template>
