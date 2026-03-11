<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Transcodes/Controllers/TranscodeController'
import TranscodeDeleteModal from '@/components/Transcodes/TranscodeDeleteModal.vue'
import TranscodeImportModal from '@/components/Transcodes/TranscodeImportModal.vue'
import VideoLayout from '@/layouts/Admin/VideoLayout.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { TranscodeCollection, Video } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'

defineProps<{
  video: Video
  items: TranscodeCollection
}>()

defineOptions({ layout: [DashboardLayout, VideoLayout] })
</script>

<template>
  <Head :title="`${video.title} - Transcodes`" />

  <UPage>
    <InfiniteScroll
      data="items"
      :buffer="200"
    >
      <UPageList divide>
        <UPageCard
          v-for="item in items?.data"
          :key="item.id"
          :to="edit.url(item.id)"
          variant="naked"
          class="py-4 first:pt-0 last:pb-0"
        >
          <div class="flex items-center justify-between">
            <UUser
              :name="item.id"
              :description="`${item.state.label} • ${item.file_size}`"
              :avatar="{
                alt: item.id,
                loading: 'lazy',
                decoding: 'async',
                class: 'rounded-sm size-12 me-1',
              }"
            />

            <div class="z-10 flex items-center gap-2">
              <TranscodeDeleteModal :item="item" />
              <TranscodeImportModal :item="item" />
            </div>
          </div>
        </UPageCard>
      </UPageList>
    </InfiniteScroll>
  </UPage>
</template>
