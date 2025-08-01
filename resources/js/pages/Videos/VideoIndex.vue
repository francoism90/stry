<script setup lang="ts">
import { index } from '@/actions/App/Web/Videos/Controllers/VideoController'
import Page from '@/components/Ui/Page.vue'
import PageBody from '@/components/Ui/PageBody.vue'
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageFilters from '@/components/Ui/PageFilters.vue'
import VideoCard from '@/components/Video/VideoCard.vue'
import { useVideos } from '@/composables/videos'
import type { Videos } from '@/types'
import { Deferred, WhenVisible } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

interface Props {
  items?: Videos
}

defineProps<Props>()

const { results, currentPage, nextPage } = useVideos()

const filters = ref<NavigationMenuItem[]>([
  { label: 'All', to: index.url(), exact: true },
  { label: 'New to you', to: index.url({ query: { list: 'unseen' } }) },
  { label: 'Newest', to: index.url({ query: { list: 'newest' } }) },
])
</script>

<template>
  <Page>
    <PageBody>
      <PageFeature title="Videos" />
      <PageFilters :filters />

      <Deferred data="items">
        <template #fallback>
          <div>Loading items...</div>
        </template>

        <div class="grid grid-cols-1 gap-4 py-2 sm:grid-cols-2 md:grid-cols-3">
          <VideoCard
            v-for="item in results"
            :key="item.id"
            :item
          />
        </div>

        <WhenVisible
          :always="nextPage !== null"
          :params="{
            only: ['items'],
            data: { page: currentPage + 1 },
          }"
        >
          <template #fallback>
            <div class="sr-only">Loading more...</div>
          </template>

          <div class="flex h-20 items-center justify-center text-sm font-medium text-neutral-400">
            <span v-if="nextPage !== null">Loading more items...</span>
          </div>
        </WhenVisible>
      </Deferred>
    </PageBody>
  </Page>
</template>
