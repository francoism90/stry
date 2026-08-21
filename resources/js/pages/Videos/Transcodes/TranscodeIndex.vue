<script setup lang="ts">
import TranscodeList from '@/components/Transcodes/TranscodeList.vue'
import { useEcho } from '@/composables/echo'
import type { TranscodeCollection, Video } from '@/types'
import { Head, InfiniteScroll, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps<{
  video: Video
  items: TranscodeCollection
}>()

const { privateChannel } = useEcho()

privateChannel(`videos.${props.video.id}`)
  .listen('.transcode.created', () => router.reload({ only: ['items'], reset: ['items'] }))
  .listen('.transcode.updated', () => router.reload({ only: ['items'], reset: ['items'] }))
  .listen('.transcode.deleted', () => router.reload({ only: ['items'], reset: ['items'] }))

const itemBody = ref()
</script>

<template>
  <Head :title="`${video.title} - Transcodes`" />

  <UPageBody>
    <InfiniteScroll
      data="items"
      :items-element="() => itemBody?.$el"
    >
      <TranscodeList
        ref="itemBody"
        :video="video"
        :items="items?.data"
        :view-all-link="false"
      />
    </InfiniteScroll>
  </UPageBody>
</template>
