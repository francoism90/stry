<script setup lang="ts">
import PageSection from '@/components/Ui/PageSection.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import ResourceLayout from '@/layouts/Video/VideoResource.vue'
import type { Video } from '@/types'

interface Props {
  video: Video
}

defineOptions({ layout: [DefaultLayout, ResourceLayout] })

defineProps<Props>()
</script>

<template>
  <PageSection>TODO</PageSection>
</template>
