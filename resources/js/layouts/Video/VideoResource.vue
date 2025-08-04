<script lang="ts" setup>
import { edit } from '@/actions/App/Web/Videos/Controllers/VideoController'
import { index } from '@/actions/App/Web/Videos/Controllers/VideoPlaylistController'
import PageBody from '@/components/Ui/PageBody.vue'
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageNavigation from '@/components/Ui/PageNavigation.vue'
import PageSection from '@/components/Ui/PageSection.vue'
import type { Video } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

interface Props {
  video: Video
}

const props = defineProps<Props>()

const items = ref<NavigationMenuItem[]>([
  { label: 'General', to: edit.url({ video: props.video.id }) },
  { label: 'Playlists', to: index.url({ video: props.video.id }) },
])
</script>

<template>
  <Head :title="video.name" />

  <PageBody>
    <PageSection>
      <PageFeature :title="video.name" />
      <PageNavigation :items />
    </PageSection>

    <slot />
  </PageBody>
</template>
