<script setup lang="ts">
import AmbientWash from '@/components/Ui/AmbientWash.vue'
import VideoEditModal from '@/components/Videos/VideoEditModal.vue'
import VideoGroupModal from '@/components/Videos/VideoGroupModal.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import VideoListSkeleton from '@/components/Videos/VideoListSkeleton.vue'
import VideoPlayer from '@/components/Videos/VideoPlayer.vue'
import VideoTags from '@/components/Videos/VideoTags.vue'
import { useSettings } from '@/composables/settings'
import { useVideo } from '@/composables/video'
import ResourceLayout from '@/layouts/App/ResourceLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { show } from '@/routes/videos'
import type { Group, Media, OptionItem, Playlist, QueryFilter, QueryValue, Transcode, Video } from '@/types'
import { Deferred, Head, router, setLayoutProps } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { ButtonProps, DropdownMenuItem } from '@nuxt/ui'
import { computed, defineOptions, defineProps, ref } from 'vue'

const props = defineProps<{
  video: Video
  playlist?: Playlist | undefined
  progress?: number | undefined
  groups?: Group[] | undefined
  media?: Media[] | undefined
  transcodes?: Transcode[] | undefined
  chapters?: OptionItem[] | undefined
  queue?: Video[] | undefined
  filter?: QueryFilter
  sort?: QueryValue
  query?: QueryValue
}>()

defineOptions({
  layout: [AppLayout, ResourceLayout],
})

setLayoutProps({
  id: 'play',
  fluid: true,
  filter: props.filter,
  sort: props.sort,
  query: props.query,
})

const isAddModalOpen = ref(false)
const isEditModalOpen = ref(false)

const { toggleLike, toggleSave } = useVideo()

const links = computed<ButtonProps[]>(() => [
  {
    label: props.video.liked ? 'Unlike' : 'Like',
    icon: props.video.liked ? 'i-lucide-heart' : 'i-lucide-heart-plus',
    active: !!props.video.liked,
    onClick: () => toggleLike(props.video),
  },
  {
    label: props.video.saved ? 'Unsave' : 'Save',
    icon: props.video.saved ? 'i-lucide-bookmark' : 'i-lucide-bookmark-plus',
    active: !!props.video.saved,
    onClick: () => toggleSave(props.video),
  },
  {
    label: 'Add',
    icon: 'i-lucide-list-plus',
    onClick: () => void (isAddModalOpen.value = true),
  },
])

const moreItems = computed<DropdownMenuItem[]>(() =>
  props.video.manage
    ? [
        {
          label: 'Edit',
          icon: 'i-lucide-edit',
          onClick: () => void (isEditModalOpen.value = true),
        },
      ]
    : [],
)

const { settings: playerSettings, get: getPlayerSetting, update: updatePlayerSettings } = useSettings('player')

const isAutoplayEnabled = computed<boolean>({
  get: () => getPlayerSetting('autoplay', true) ?? true,
  // The settings action replaces the whole player group, so send the current values along.
  set: (autoplay) => updatePlayerSettings({ ...playerSettings.value, autoplay }),
})

const nextVideo = computed<Video | undefined>(() => props.queue?.[0])

const playNext = (): void => {
  if (isAutoplayEnabled.value && nextVideo.value) {
    router.visit(show.url(nextVideo.value.id))
  }
}

const videoChannel = `videos.${props.video.id}`

useEcho(videoChannel, '.videos.updated', () => router.reload({ only: ['video', 'queue'] }))
useEcho(videoChannel, '.videos.trashed', () => router.visit('/'))
useEcho(videoChannel, ['.transcode.created', '.transcode.updated', '.transcode.deleted'], () =>
  router.reload({ only: ['transcodes'] }),
)
useEcho(videoChannel, ['.media.created', '.media.updated', '.media.deleted'], () =>
  router.reload({ only: ['media', 'playlist'] }),
)
</script>

<template>
  <Head :title="video.title" />

  <AmbientWash
    :images="video.thumb ? [video.thumb] : []"
    tall
  />

  <UPage class="mt-2">
    <VideoPlayer
      :video="video"
      :playlist="playlist"
      :progress="progress"
      @ended="playNext"
    />

    <VideoGroupModal
      v-model:open="isAddModalOpen"
      :video="video"
      :groups="groups"
    />

    <VideoEditModal
      v-if="video.manage"
      v-model:open="isEditModalOpen"
      :video="video"
      :progress="progress"
      :media="media"
      :transcodes="transcodes"
      :chapters="chapters"
    />

    <UPageHeader
      :title="video.title"
      :ui="{
        root: 'pt-4 pb-0',
        headline: 'mb-0',
        wrapper: 'flex-row flex-wrap items-start justify-between gap-x-4 gap-y-3 lg:items-start',
        title: 'min-w-0 flex-1 basis-80 text-xl wrap-anywhere capitalize sm:text-2xl',
        links: 'flex-wrap gap-1.5 pt-0.5',
        description: 'mt-3 flex flex-col gap-3 text-base',
      }"
    >
      <template #links>
        <UButton
          v-for="link in links"
          :key="link.label"
          v-bind="link"
          color="neutral"
          variant="outline"
          size="xs"
          class="rounded-full bg-(--glass) ps-2 pe-2.5 text-highlighted ring-(--glass-border) backdrop-blur-md backdrop-saturate-140 hover:bg-(--glass-strong)"
          :class="{ 'bg-(--glass-strong)': link.active }"
          :aria-pressed="link.active"
        />

        <UDropdownMenu
          v-if="moreItems.length"
          :items="moreItems"
          :content="{ align: 'end' }"
        >
          <UButton
            icon="i-lucide-ellipsis"
            aria-label="More actions"
            color="neutral"
            variant="outline"
            size="xs"
            class="rounded-full bg-(--glass) text-highlighted ring-(--glass-border) backdrop-blur-md backdrop-saturate-140 hover:bg-(--glass-strong)"
          />
        </UDropdownMenu>
      </template>

      <template #description>
        <p
          v-if="video.description?.length"
          v-html="video.description"
        />

        <VideoTags
          :items="video.tags"
          variant="chips"
        />
      </template>
    </UPageHeader>

    <UPageBody class="mt-8 flex flex-col gap-3">
      <section
        aria-labelledby="up-next"
        class="flex flex-col gap-3"
      >
        <div class="flex items-center justify-between gap-3">
          <h2
            id="up-next"
            class="text-base font-semibold text-highlighted"
          >
            Up next
          </h2>

          <USwitch
            v-model="isAutoplayEnabled"
            label="Autoplay"
            size="sm"
            :ui="{ root: 'flex-row-reverse gap-2', label: 'text-xs font-normal text-muted' }"
          />
        </div>

        <Deferred data="queue">
          <template #fallback>
            <VideoListSkeleton :count="4" />
          </template>

          <VideoList
            :items="queue"
            :lead-label="isAutoplayEnabled ? 'Playing next' : undefined"
          />
        </Deferred>
      </section>
    </UPageBody>
  </UPage>
</template>
