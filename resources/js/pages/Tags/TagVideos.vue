<script setup lang="ts">
import PageSection from '@/components/Ui/PageSection.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import VideoCollection from '@/layouts/Video/VideoCollection.vue'
import type { Video } from '@/types'

interface Props {
  video: Video
}

defineOptions({ layout: [DefaultLayout, VideoCollection] })

defineProps<Props>()
</script>

<template>
  <PageSection>playlists</PageSection>
</template>
