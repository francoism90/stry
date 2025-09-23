<script lang="ts" setup>
import { edit, show } from '@/actions/App/Web/Videos/Controllers/VideoController'
import { index } from '@/actions/App/Web/Videos/Controllers/VideoPlaylistController'
import PageBody from '@/components/Ui/PageBody.vue'
import PageNavigation from '@/components/Ui/PageNavigation.vue'
import PageSection from '@/components/Ui/PageSection.vue'
import type { Video } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { NavigationMenuItem } from '@nuxt/ui'
import { useDateFormat } from '@vueuse/core'
import { computed, ref } from 'vue'

interface Props {
  video: Video
}

const props = defineProps<Props>()

const links = ref<NavigationMenuItem[]>([{ label: 'View', icon: 'i-lucide-file', to: show.url(props.video.id) }])

const tabs = ref<NavigationMenuItem[]>([
  { label: 'General', to: edit.url(props.video.id) },
  { label: 'Playlists', to: index.url({ video: props.video.id }) },
])

const details = computed(() => [useDateFormat(props.video.updated_at, 'YYYY-MM-DD HH:mm:ss').value, props.video.timestamp ?? 'N/A'].filter(Boolean).join(' • '))

useEcho<Video>(`videos.${props.video.id}`, '.video.updated', () => router.reload())
useEcho<Video>(`videos.${props.video.id}`, '.playlist.updated', () => router.reload())
</script>

<template>
  <Head :title="video.title" />

  <PageBody>
    <PageSection>
      <UPageHeader
        :ui="{ root: 'border-0 py-0 text-sm tracking-tight text-neutral-300', title: 'text-sm sm:text-xl', description: 'mt-0 text-sm' }"
        :title="video.title"
        :description="details"
        :links="links"
      />

      <PageNavigation :tabs />
    </PageSection>

    <slot />
  </PageBody>
</template>
