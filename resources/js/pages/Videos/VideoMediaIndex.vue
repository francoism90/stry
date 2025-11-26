<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import VideoLayout from '@/layouts/Video/VideoLayout.vue'
import type { Video } from '@/types'

interface Props {
  video: Video
  progress: number | undefined
}

defineOptions({ layout: [DashboardLayout, VideoLayout] })

defineProps<Props>()
</script>

<template>
  <div>media</div>
</template>
