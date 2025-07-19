<script setup lang="ts">
import Page from '@/components/Ui/Page.vue'
import PageBody from '@/components/Ui/PageBody.vue'
import VideoSection from '@/components/Video/VideoSection.vue'
import type { Video } from '@/types'
import { Deferred } from '@inertiajs/vue3'

interface Props {
  recent?: Video[]
}

defineProps<Props>()
</script>

<template>
  <Page>
    <PageBody>
      <Deferred :data="['recent']">
        <template #fallback>
          <div class="sr-only">Loading sections...</div>
        </template>

        <VideoSection
          v-if="recent?.length"
          label="Continue Watching"
          :items="recent"
          :actions="[{ label: 'All Watching', href: '/videos', trailingIcon: 'i-lucide-chevron-right' }]"
        />
      </Deferred>
    </PageBody>
  </Page>
</template>
