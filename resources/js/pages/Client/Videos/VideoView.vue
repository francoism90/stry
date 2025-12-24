<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Videos/Controllers/VideoController'
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

  <UDashboardPanel id="play">
    <template #header>
      <UDashboardNavbar
        title="Watch Video"
        :ui="{ root: 'h-24 gap-3 border-0', left: 'w-full' }"
        :toggle="{ variant: 'link', class: 'ps-0' }"
      >
        <template #right>
          <UButton
            v-for="link in links"
            :key="link.label"
            v-bind="link"
            size="xs"
            variant="soft"
          />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <UPage>
        <VideoPlayer />

        <UPageHeader
          title="Video"
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

            <div class="flex items-center gap-x-2 overflow-auto">
              <UButton
                v-for="tag in video.tags"
                :key="tag.id"
                variant="outline"
                size="sm"
                class="mt-2"
                :label="tag.name"
              />
            </div>
          </template>
        </UPageHeader>

        <Deferred data="queue">
          <template #fallback>
            <div class="sr-only">Loading queue...</div>
          </template>

          <UPageFeature title="Up next" />
          <VideoList :items="queue" />
        </Deferred>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
