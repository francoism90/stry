<script setup lang="ts">
import Page from '@/components/Ui/Page.vue'
import PageBody from '@/components/Ui/PageBody.vue'
import VideoCard from '@/components/Video/VideoCard.vue'
import { useVideos } from '@/composables/videos'
import type { Videos } from '@/types'
import { Deferred, router, usePage, WhenVisible } from '@inertiajs/vue3'

interface Props {
  items?: Videos
}

defineProps<Props>()

const { data, hasPages, nextPage } = useVideos()

const fetch = () => router.get(usePage().props.location, { page: nextPage.value })
</script>

<template>
  <Page>
    <PageBody>
      <slot />

      <Deferred data="items">
        <template #fallback>
          <div class="sr-only">Loading items...</div>
        </template>

        <div class="flex flex-col gap-4">
          <div class="grid grid-cols-1 gap-4 py-2 sm:grid-cols-2 md:grid-cols-3">
            <VideoCard
              v-for="item in data"
              :key="item.id"
              :item
            />
          </div>
        </div>

        <WhenVisible
          :always="hasPages"
          :buffer="100"
          :params="{
            only: ['items'],
            data: hasPages ? { page: nextPage } : {},
          }"
        >
          <template #fallback>
            <div class="sr-only">Loading more...</div>
          </template>

          <div
            v-if="hasPages"
            class="flex h-20 w-full items-center justify-center"
          >
            <UButton
              label="Load more"
              variant="soft"
              @click="fetch"
            />
          </div>
        </WhenVisible>
      </Deferred>
    </PageBody>
  </Page>
</template>
