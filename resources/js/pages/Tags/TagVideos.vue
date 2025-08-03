<script setup lang="ts">
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageSection from '@/components/Ui/PageSection.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import VideoCollection from '@/layouts/Video/VideoCollection.vue'
import type { Tag } from '@/types'
import { Head } from '@inertiajs/vue3'

interface Props {
  tag: Tag
}

defineOptions({ layout: [DefaultLayout, VideoCollection] })

defineProps<Props>()
</script>

<template>
  <Head :title="tag.name" />

  <PageSection>
    <PageFeature :title="tag.name">
      <dl class="list text-xs font-light tracking-tight text-neutral-100">
        <dt class="sr-only">Type</dt>
        <dd>{{ tag.type }}</dd>
        <dt class="sr-only">Videos</dt>
        <dd>{{ tag.videos }} videos</dd>
      </dl>
    </PageFeature>
  </PageSection>
</template>
