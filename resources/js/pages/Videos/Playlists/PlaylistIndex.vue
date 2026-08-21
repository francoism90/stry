<script setup lang="ts">
import PlaylistCreateModal from '@/components/Playlists/PlaylistCreateModal.vue'
import PlaylistDeleteModal from '@/components/Playlists/PlaylistDeleteModal.vue'
import ActionBar from '@/components/Ui/ActionBar.vue'
import { useEcho } from '@/composables/echo'
import type { PlaylistCollection, Video } from '@/types'
import { Head, InfiniteScroll, router } from '@inertiajs/vue3'

const props = defineProps<{
  video: Video
  items: PlaylistCollection
}>()

const { privateChannel } = useEcho()

privateChannel(`videos.${props.video.id}`)
  .listen('.playlist.created', () => router.reload({ only: ['items'], reset: ['items'] }))
  .listen('.playlist.updated', () => router.reload({ only: ['items'], reset: ['items'] }))
  .listen('.playlist.deleted', () => router.reload({ only: ['items'], reset: ['items'] }))
</script>

<template>
  <Head :title="`${video.title} - Playlists`" />

  <UPageBody>
    <ActionBar>
      <template #left>
        <PlaylistCreateModal :video="video" />
      </template>
    </ActionBar>

    <InfiniteScroll
      data="items"
      items-element="#infinite-items"
      :buffer="200"
    >
      <div id="infinite-items">
        <UEmpty
          v-if="!items?.data?.length"
          icon="i-lucide-list-video"
          title="No playlists"
          description="Create a playlist to enable streaming for this video."
        />

        <UPageList
          v-else
          divide
        >
          <UPageCard
            v-for="item in items?.data"
            :key="item.id"
            variant="naked"
            class="py-4 first:pt-0 last:pb-0"
          >
            <div class="flex items-center justify-between">
              <UUser
                :name="item.id"
                :description="`${item.state.label} · ${item.type}`"
                :avatar="{
                  alt: item.id,
                  loading: 'lazy',
                  decoding: 'async',
                  class: 'rounded-sm size-12 me-1',
                }"
              />

              <div class="z-10 flex items-center gap-2">
                <PlaylistDeleteModal :item="item" />
              </div>
            </div>
          </UPageCard>
        </UPageList>
      </div>
    </InfiniteScroll>
  </UPageBody>
</template>
