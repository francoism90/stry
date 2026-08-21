<script setup lang="ts">
import MediaDeleteModal from '@/components/Media/MediaDeleteModal.vue'
import MediaViewModal from '@/components/Media/MediaViewModal.vue'
import { useMedia } from '@/composables/media'
import { index as mediaIndex } from '@/routes/videos/media'
import type { Media, Video } from '@/types'

defineProps<{
  video: Video
  items?: Media[] | undefined
}>()

const { getStreamInfo } = useMedia()
</script>

<template>
  <div class="flex flex-col gap-3">
    <div class="flex items-center justify-end">
      <UButton
        label="View all"
        trailing-icon="i-lucide-arrow-right"
        color="neutral"
        variant="link"
        size="sm"
        :to="mediaIndex.url(video.id)"
      />
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
            :name="item.file_name"
            :description="getStreamInfo(item).join(' · ')"
            :avatar="{
              alt: item.file_name,
              loading: 'lazy',
              decoding: 'async',
              class: 'rounded-sm size-10 me-1',
            }"
          />

          <div class="z-10 flex items-center gap-2">
            <MediaViewModal :item="item" />
            <MediaDeleteModal :item="item" />
          </div>
        </div>
      </UPageCard>
    </UPageList>
  </div>
</template>
