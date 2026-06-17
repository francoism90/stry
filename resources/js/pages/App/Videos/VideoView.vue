<script setup lang="ts">
import { edit } from '@/actions/App/Web/Videos/Controllers/VideoController'
import GroupVideoModal from '@/components/Groups/GroupVideoModal.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import VideoPlayer from '@/components/Videos/VideoPlayer.vue'
import VideoTags from '@/components/Videos/VideoTags.vue'
import { useEcho } from '@/composables/echo'
import { useVideo } from '@/composables/video'
import { index } from '@/routes/videos'
import type { Group, Video } from '@/types'
import { Deferred, Head, router } from '@inertiajs/vue3'
import type { ButtonProps } from '@nuxt/ui'
import { computed, ref } from 'vue'

const props = defineProps<{
  video: Video
  queue?: Video[] | undefined
  groups?: Group[] | undefined
}>()

const isAddModalOpen = ref(false)

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
  {
    label: 'Add',
    icon: 'i-lucide-list-plus',
    onClick: () => void (isAddModalOpen.value = true),
  },
])

const { listen } = useEcho()

listen<Video>(`videos.${props.video.id}`, '.video.updated', () => router.reload({ only: ['video'] }))
listen<Video>(`videos.${props.video.id}`, '.video.trashed', () => router.visit(index.url()))
listen<Video>(`videos.${props.video.id}`, '.playlist.created', () => router.reload({ only: ['playlist'] }))
listen<Video>(`videos.${props.video.id}`, '.playlist.updated', () => router.reload({ only: ['playlist'] }))
listen<Video>(`videos.${props.video.id}`, '.playlist.deleted', () => router.reload({ only: ['playlist'] }))
</script>

<template>
  <Head :title="video.title" />

  <UDashboardPanel id="play">
    <template #header>
      <AppHeader />
    </template>

    <template #body>
      <UPage class="mt-6">
        <VideoPlayer />

        <GroupVideoModal
          v-model:open="isAddModalOpen"
          :video="video"
          :groups="groups"
        />

        <UPageHeader
          :title="video.title"
          :links="links"
          :ui="{
            title: 'text-xl wrap-anywhere capitalize sm:text-2xl',
          }"
        >
          <template #description>
            <p
              v-if="video.description?.length"
              v-html="video.description"
              class="mb-2"
            />

            <VideoTags :items="video.tags" />
          </template>
        </UPageHeader>

        <Deferred data="queue">
          <template #fallback>
            <div class="sr-only">Loading queue...</div>
          </template>

          <UPageBody>
            <UPageFeature
              title="Up next"
              class="mt-6"
            />

            <VideoList :items="queue" />
          </UPageBody>
        </Deferred>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
