<script setup lang="ts">
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import VideoGroupModal from '@/components/Videos/VideoGroupModal.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import VideoPlayer from '@/components/Videos/VideoPlayer.vue'
import VideoTags from '@/components/Videos/VideoTags.vue'
import { useEcho } from '@/composables/echo'
import { useVideo } from '@/composables/video'
import type { Group, Playlist, Video } from '@/types'
import { Deferred, Head, router } from '@inertiajs/vue3'
import type { ButtonProps } from '@nuxt/ui'
import { computed, ref } from 'vue'

const props = defineProps<{
  video: Video
  playlist?: Playlist | undefined
  progress?: number | undefined
  queue?: Video[] | undefined
  groups?: Group[] | undefined
}>()

const isAddModalOpen = ref(false)

const { toggleLike, toggleSave } = useVideo()
const { privateChannel } = useEcho()

const links = computed<ButtonProps[]>(() => [
  {
    label: 'Edit',
    icon: 'i-lucide-edit',
    // to: edit.url(props.video.id),
  },
  {
    label: props.video.liked ? 'Unlike' : 'Like',
    icon: props.video.liked ? 'i-lucide-heart' : 'i-lucide-heart-plus',
    onClick: () => toggleLike(props.video),
  },
  {
    label: props.video.saved ? 'Unsave' : 'Save',
    icon: props.video.saved ? 'i-lucide-bookmark' : 'i-lucide-bookmark-plus',
    onClick: () => toggleSave(props.video),
  },
  {
    label: 'Add',
    icon: 'i-lucide-list-plus',
    onClick: () => void (isAddModalOpen.value = true),
  },
])

privateChannel(`videos.${props.video.id}`)
  .listen('.videos.updated', () => router.reload({ only: ['video'] }))
  .listen('.videos.trashed', () => router.visit('/'))
  .listen('.playlist.created', () => router.reload({ only: ['playlist'] }))
  .listen('.playlist.updated', () => router.reload({ only: ['playlist'] }))
  .listen('.playlist.deleted', () => router.reload({ only: ['playlist'] }))
</script>

<template>
  <Head :title="video.title" />

  <UDashboardPanel id="play">
    <template #header>
      <AppHeader />
    </template>

    <template #body>
      <UPage class="mt-6">
        <VideoPlayer
          :video="video"
          :playlist="playlist"
          :progress="progress"
        />

        <VideoGroupModal
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

    <template #footer>
      <AppFooter />
    </template>
  </UDashboardPanel>
</template>
