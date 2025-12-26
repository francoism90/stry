<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Videos/Controllers/VideoController'
import HomeController from '@/actions/App/Client/Account/Controllers/HomeController'
import VideoList from '@/components/Videos/VideoList.vue'
import VideoPlayer from '@/components/Videos/VideoPlayer.vue'
import { useVideos } from '@/composables/videos'
import type { Video, VideoCollection } from '@/types'
import { Deferred, Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { ButtonProps } from '@nuxt/ui'
import { computed } from 'vue'

const props = defineProps<{
  video: Video
  queue?: VideoCollection
}>()

const { toggleGroup } = useVideos()

const back = () => (window && window.history?.length > 1 ? window.history.back() : router.visit('/'))

const links = computed<ButtonProps[]>(() => [
  {
    label: props.video.favorited ? 'Unfavorite' : 'Favorite',
    icon: props.video.favorited ? 'i-lucide-heart' : 'i-lucide-heart-plus',
    onClick: () => toggleGroup(props.video, 'favorited'),
  },
  {
    label: props.video.saved ? 'Unsave' : 'Save',
    icon: props.video.saved ? 'i-lucide-bookmark' : 'i-lucide-bookmark-plus',
    onClick: () => toggleGroup(props.video, 'saved'),
  },
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

  <UDashboardPanel id="play">
    <template #header>
      <UDashboardNavbar
        :ui="{ root: 'h-24 gap-3 border-0' }"
        :toggle="{ variant: 'link', class: 'ps-0' }"
      >
        <template #right>
          <UButton
            label="Back"
            @click="back"
            variant="outline"
            size="xs"
            color="neutral"
            class="p-3"
            icon="i-lucide-arrow-left"
          />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <UPage>
        <VideoPlayer />

        <UPageHeader
          :title="video.title"
          :links="links"
          :ui="{
            root: 'py-4',
            title: 'font-serif text-xl sm:text-2xl',
            description: 'text-base',
          }"
        >
          <template #description>
            <p
              v-if="video.description?.length"
              v-html="video.description"
            />

            <div class="flex items-center gap-2 overflow-auto">
              <UButton
                v-for="tag in video.tags"
                :key="tag.id"
                :label="tag.name"
                :to="HomeController.url('all', { query: { search: tag.name } })"
                variant="outline"
                size="sm"
                class="mt-2"
              />
            </div>
          </template>
        </UPageHeader>

        <UPageBody>
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
