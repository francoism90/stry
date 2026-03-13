<script setup lang="ts">
import { edit } from '@/actions/App/Web/Videos/Controllers/VideoController'
import AppHeader from '@/components/Ui/AppHeader.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import VideoPlayer from '@/components/Videos/VideoPlayer.vue'
import VideoTags from '@/components/Videos/VideoTags.vue'
import { useVideo } from '@/composables/video'
import type { Video } from '@/types'
import { Deferred, Head } from '@inertiajs/vue3'
import type { ButtonProps } from '@nuxt/ui'
import { computed } from 'vue'

const props = defineProps<{
  video: Video
  queue?: Video[] | undefined
}>()

const { toggleLike, toggleSave } = useVideo(props.video)

const links = computed<ButtonProps[]>(() => [
  {
    label: 'Edit',
    icon: 'i-lucide-edit',
    to: edit.url(props.video.id),
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
</script>

<template>
  <Head :title="video.title" />

  <UDashboardPanel id="play">
    <template #header>
      <AppHeader />
    </template>

    <template #body>
      <UPage>
        <UPageBody class="mt-6">
          <VideoPlayer />

          <UPageHeader
            :title="video.title"
            :links="links"
            :ui="{
              root: 'pt-0',
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
