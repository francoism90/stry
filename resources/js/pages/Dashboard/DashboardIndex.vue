<script setup lang="ts">
import { index } from '@/actions/App/Web/Videos/Controllers/VideoController'
import PageBody from '@/components/Ui/PageBody.vue'
import PageSection from '@/components/Ui/PageSection.vue'
import VideoCarousel from '@/components/Video/VideoCarousel.vue'
import type { Video } from '@/types'
import { Deferred } from '@inertiajs/vue3'

interface Props {
  recommended?: Video[]
  recent?: Video[]
  watching?: Video[]
}

defineProps<Props>()
</script>

<template>
  <PageBody>
    <Deferred :data="['recommended', 'recent', 'watching']">
      <template #fallback>
        <div class="sr-only">Loading sections...</div>
      </template>

      <PageSection class="gap-8">
        <VideoCarousel
          label="Made for You"
          :items="recommended"
          :actions="[{ label: 'Show All', href: index.url(), trailingIcon: 'i-lucide-chevron-right' }]"
        />

        <VideoCarousel
          label="Continue Watching"
          :items="watching"
          :actions="[{ label: 'Show All', href: index.url({ query: { list: 'watching' } }), trailingIcon: 'i-lucide-chevron-right' }]"
        />

        <VideoCarousel
          label="New Releases"
          :items="recent"
          :actions="[{ label: 'Show All', href: index.url({ query: { list: 'newest' } }), trailingIcon: 'i-lucide-chevron-right' }]"
        />
      </PageSection>
    </Deferred>
  </PageBody>
</template>
