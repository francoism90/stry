<script setup lang="ts">
import VideoTranscodeController from '@/actions/App/Api/Videos/Controllers/VideoTranscodeController'
import TranscodeDeleteModal from '@/components/Transcodes/TranscodeDeleteModal.vue'
import TranscodeImportModal from '@/components/Transcodes/TranscodeImportModal.vue'
import ActionBar from '@/components/Ui/ActionBar.vue'
import VideoLayout from '@/layouts/App/VideoLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { TranscodeCollection, Video } from '@/types'
import { Head, InfiniteScroll, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'

defineOptions({ layout: [DefaultLayout, VideoLayout] })

const props = defineProps<{
  video: Video
  items: TranscodeCollection
}>()

const createTranscode = () => router.post(VideoTranscodeController.url(props.video.id), {}, { preserveScroll: true })

useEcho<Video>(`videos.${props.video.id}`, '.transcode.created', () =>
  router.reload({ only: ['items'], reset: ['items'] }),
)
useEcho<Video>(`videos.${props.video.id}`, '.transcode.updated', () =>
  router.reload({ only: ['items'], reset: ['items'] }),
)
useEcho<Video>(`videos.${props.video.id}`, '.transcode.deleted', () =>
  router.reload({ only: ['items'], reset: ['items'] }),
)
</script>

<template>
  <Head :title="`${video.title} - Transcodes`" />

  <UPageBody>
    <ActionBar>
      <template #left>
        <TranscodeImportModal :video="video" />

        <UButton
          icon="i-lucide-plus"
          label="Create transcode"
          color="neutral"
          variant="outline"
          size="sm"
          @click="createTranscode"
        />
      </template>
    </ActionBar>

    <InfiniteScroll
      data="items"
      items-element="#infinite-items"
      :buffer="200"
    >
      <UEmpty
        v-if="!items?.data?.length"
        icon="i-lucide-cpu"
        title="No transcodes"
        description="Transcode this video to AV1 to possibly reduce file size while maintaining quality."
      />

      <UPageList
        v-else
        id="infinite-items"
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
              :description="`${item.state.label} · ${item.file_size}`"
              :avatar="{
                alt: item.id,
                loading: 'lazy',
                decoding: 'async',
                class: 'rounded-sm size-12 me-1',
              }"
            />

            <div class="z-10 flex items-center gap-2">
              <TranscodeDeleteModal :item="item" />
            </div>
          </div>
        </UPageCard>
      </UPageList>
    </InfiniteScroll>
  </UPageBody>
</template>
