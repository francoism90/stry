<script setup lang="ts">
import Page from '@/components/Ui/Page.vue'
import PageBody from '@/components/Ui/PageBody.vue'
import VideoCarousel from '@/components/Video/VideoCarousel.vue'
import type { Video } from '@/types'
import { Deferred } from '@inertiajs/vue3'

interface Props {
  recent?: Video[]
}

defineProps<Props>()
</script>

<template>
  <Page>
    <PageBody class="gap-8">
      <Deferred :data="['recent']">
        <template #fallback>
          <div class="sr-only">Loading sections...</div>
        </template>

        <VideoCarousel
          label="Made for You"
          :items="recent"
          :actions="[{ label: 'Show All', href: '/videos', trailingIcon: 'i-lucide-chevron-right' }]"
        />

        <VideoCarousel
          label="Continue Watching"
          :items="recent"
          :actions="[{ label: 'Show All', href: '/videos', trailingIcon: 'i-lucide-chevron-right' }]"
        />
      </Deferred>
    </PageBody>
  </Page>
</template>
