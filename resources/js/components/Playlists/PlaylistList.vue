<script setup lang="ts">
import PlaylistCreateModal from '@/components/Playlists/PlaylistCreateModal.vue'
import PlaylistDeleteModal from '@/components/Playlists/PlaylistDeleteModal.vue'
import type { Playlist, Video } from '@/types'

defineProps<{
  video?: Video
  items?: Playlist[] | undefined
}>()
</script>

<template>
  <div class="flex flex-col gap-3">
    <div
      v-if="video"
      class="flex items-center gap-2"
    >
      <PlaylistCreateModal :video="video" />
    </div>

    <div
      v-if="items === undefined"
      class="flex flex-col gap-2"
    >
      <USkeleton
        v-for="i in 3"
        :key="i"
        class="h-14 w-full rounded-md"
      />
    </div>

    <UPageList
      v-else-if="items.length"
      divide
    >
      <UPageCard
        v-for="item in items"
        :key="item.id"
        variant="naked"
        class="py-3 first:pt-0 last:pb-0"
      >
        <div class="flex items-center justify-between">
          <UUser
            :name="video ? item.id : (item.resource?.label ?? item.id)"
            :description="`${item.state.label} · ${item.type}`"
            :avatar="{
              alt: item.id,
              loading: 'lazy',
              decoding: 'async',
              class: 'rounded-sm size-10 me-1',
            }"
          />

          <div class="z-10 flex items-center gap-2">
            <PlaylistDeleteModal :item="item" />
          </div>
        </div>
      </UPageCard>
    </UPageList>
  </div>
</template>
