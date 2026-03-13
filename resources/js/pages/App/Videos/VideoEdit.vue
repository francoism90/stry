<script setup lang="ts">
import VideoLayout from '@/layouts/App/VideoLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { Video } from '@/types'
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps<{
  video: Video
}>()

defineOptions({ layout: [DefaultLayout, VideoLayout] })

const meta = computed(() => [props.video.timestamp, props.video.filesize, props.video.user?.name].filter(Boolean))
</script>

<template>
  <Head :title="video.title" />

  <UPage>
    <UPageHeader :title="video.title">
      <template #description>
        <div class="dot-separated text-muted flex flex-wrap items-center text-sm">
          <span
            v-for="(item, index) in meta"
            :key="index"
          >
            {{ item }}
          </span>
        </div>
      </template>
    </UPageHeader>
  </UPage>
</template>
