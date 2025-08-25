<script lang="ts" setup>
import { edit, show } from '@/actions/App/Web/Videos/Controllers/VideoController'
import { index } from '@/actions/App/Web/Videos/Controllers/VideoPlaylistController'
import PageActions from '@/components/Ui/PageActions.vue'
import PageBody from '@/components/Ui/PageBody.vue'
import PageColumns from '@/components/Ui/PageColumns.vue'
import PageDetails from '@/components/Ui/PageDetails.vue'
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageNavigation from '@/components/Ui/PageNavigation.vue'
import PageSection from '@/components/Ui/PageSection.vue'
import type { Video } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { NavigationMenuItem } from '@nuxt/ui'
import { useDateFormat } from '@vueuse/core'
import { ref } from 'vue'

interface Props {
  video: Video
}

const props = defineProps<Props>()

const details = ref<NavigationMenuItem[]>([
  { label: 'Created', value: useDateFormat(props.video.created_at, 'YYYY-MM-DD HH:mm:ss').value },
  { label: 'Duration', value: props.video.timestamp ?? 'Unknown' },
])

const actions = ref<NavigationMenuItem[]>([{ label: 'View', icon: 'i-lucide-file', to: show.url(props.video.id) }])

const tabs = ref<NavigationMenuItem[]>([
  { label: 'General', to: edit.url(props.video.id) },
  { label: 'Playlists', to: index.url({ video: props.video.id }) },
])

useEcho<Video>(`videos.${props.video.id}`, '.video.updated', () => router.reload())
useEcho<Video>(`videos.${props.video.id}`, '.playlist.updated', () => router.reload())
</script>

<template>
  <Head :title="video.title" />

  <PageBody>
    <PageSection>
      <PageColumns>
        <template #left>
          <PageFeature :title="video.title" />
          <PageDetails :details />
        </template>

        <template #right>
          <PageActions :actions />
        </template>
      </PageColumns>

      <PageNavigation :tabs />
    </PageSection>

    <slot />
  </PageBody>
</template>
