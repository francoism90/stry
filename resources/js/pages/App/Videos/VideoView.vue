<script setup lang="ts">
import VideoList from '@/components/Videos/VideoList.vue'
import VideoPlayer from '@/components/Videos/VideoPlayer.vue'
import VideoTags from '@/components/Videos/VideoTags.vue'
import { useVideo } from '@/composables/video'
import VideoLayout from '@/layouts/App/VideoLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { Video } from '@/types'
import { Deferred, Head } from '@inertiajs/vue3'
import type { ButtonProps } from '@nuxt/ui'
import { computed } from 'vue'

const props = defineProps<{
  video: Video
  queue?: Video[]
}>()

defineOptions({ layout: [DefaultLayout, VideoLayout] })

const { toggleLike, toggleSave } = useVideo(props.video)

const links = computed<ButtonProps[]>(() => [
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
</script>

<template>
  <Head :title="video.title" />

  <UPage>
    <VideoPlayer />

    <UPageHeader
      :title="video.title"
      :links="links"
      :ui="{
        title: 'text-xl sm:text-2xl',
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
