<script setup lang="ts">
import { edit } from '@/actions/App/Web/Videos/Controllers/VideoController'
import PageActions from '@/components/Ui/PageActions.vue'
import PageColumns from '@/components/Ui/PageColumns.vue'
import PageSection from '@/components/Ui/PageSection.vue'
import PageTags from '@/components/Ui/PageTags.vue'
import VideoCarousel from '@/components/Video/VideoCarousel.vue'
import VideoPlayer from '@/components/Video/VideoPlayer.vue'
import type { Video } from '@/types'
import { Deferred, Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { NavigationMenuItem } from '@nuxt/ui'
import { useDateFormat } from '@vueuse/core'
import { computed, ref } from 'vue'

interface Props {
  video: Video
  queue?: Video[] | null
}

const props = defineProps<Props>()

const actions = ref<NavigationMenuItem[]>([
  { label: '0', icon: 'i-lucide-thumbs-up', to: '/tags' },
  { label: 'Edit', icon: 'i-lucide-clipboard-pen', to: edit.url(props.video.id) },
  { label: 'Save', icon: 'i-lucide-bookmark', to: '/tags' },
])

const details = computed(() =>
  [useDateFormat(props.video.updated_at, 'YYYY-MM-DD HH:mm:ss').value, props.video.timestamp ?? 'N/A'].filter(Boolean).join(' • '),
)

useEcho<Video>(`videos.${props.video.id}`, '.video.updated', () => router.reload({ only: ['video'] }))
useEcho<Video>(`videos.${props.video.id}`, '.playlist.created', () => router.reload({ only: ['playlist'] }))
useEcho<Video>(`videos.${props.video.id}`, '.playlist.updated', () => router.reload({ only: ['playlist'] }))
</script>

<template>
  <Head :title="video.title" />

  <UPageBody>
    <VideoPlayer />

    <PageSection class="gap-4 py-2">
      <PageColumns>
        <template #left>
          <UPageFeature
            :title="video.title"
            :description="details"
          />

          <PageTags :badges="video.tags" />
        </template>

        <template #right>
          <PageActions :actions />
        </template>
      </PageColumns>

      <Deferred :data="['queue']">
        <template #fallback>
          <div class="sr-only">Loading sections...</div>
        </template>

        <VideoCarousel
          label="Up Next"
          :items="queue"
        />
      </Deferred>
    </PageSection>
  </UPageBody>
</template>
