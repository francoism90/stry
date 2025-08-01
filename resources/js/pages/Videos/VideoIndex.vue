<script setup lang="ts">
import { index } from '@/actions/App/Web/Videos/Controllers/VideoController'
import Page from '@/components/Ui/Page.vue'
import PageBody from '@/components/Ui/PageBody.vue'
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageFilters from '@/components/Ui/PageFilters.vue'
import VideoCard from '@/components/Video/VideoCard.vue'
import type { Video } from '@/types'
import { Deferred } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

interface Props {
  items?: Video[] | null
}

defineProps<Props>()

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

      <Deferred :data="['items']">
        <template #fallback>
          <div class="sr-only">Loading items...</div>
        </template>

        <div class="grid grid-cols-1 gap-4 py-2 sm:grid-cols-2 md:grid-cols-3">
          <VideoCard
            v-for="item in items"
            :key="item.id"
            :item
          />
        </div>
      </Deferred>
    </PageBody>
  </Page>
</template>
