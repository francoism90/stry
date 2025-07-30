<script setup lang="ts">
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import VideoLayout from '@/layouts/Video/ManageLayout.vue'
import type { Video } from '@/types'

interface Props {
  video: Video
}

defineOptions({ layout: [DefaultLayout, VideoLayout] })

defineProps<Props>()
</script>

<template>
  <div>playlists</div>
</template>
