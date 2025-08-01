<script setup lang="ts">
import { index } from '@/actions/App/Web/Videos/Controllers/VideoController'
import Page from '@/components/Ui/Page.vue'
import PageBody from '@/components/Ui/PageBody.vue'
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageFilters from '@/components/Ui/PageFilters.vue'
import VideoList from '@/components/Video/VideoList.vue'
import { useVideos } from '@/composables/videos'
import type { Videos } from '@/types'
import { Deferred, Head } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

interface Props {
  items?: Videos
}

defineProps<Props>()

const { results, currentPage, nextPage } = useVideos()

const filters = ref<NavigationMenuItem[]>([
  { label: 'All', to: index.url({ query: { list: 'all' } }) },
  { label: 'Watched', to: index.url({ query: { list: 'watching' } }) },
  { label: 'Newest', to: index.url({ query: { list: 'newest' } }) },
])
</script>

<template>
  <Head title="Videos" />

  <Page>
    <PageBody>
      <PageFeature title="Videos" />
      <PageFilters :items="filters" />

      <Deferred data="items">
        <template #fallback>
          <div class="sr-only">Loading items...</div>
        </template>

        <VideoList
          :results
          :currentPage
          :nextPage
        />
      </Deferred>
    </PageBody>
  </Page>
</template>
