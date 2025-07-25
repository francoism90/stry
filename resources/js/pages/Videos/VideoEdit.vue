<script setup lang="ts">
import Page from '@/components/Ui/Page.vue'
import PageBody from '@/components/Ui/PageBody.vue'
import PageFeature from '@/components/Ui/PageFeature.vue'
import type { Playlist, Video } from '@/types'
import { Head } from '@inertiajs/vue3'

interface Props {
  item: Video
  assets: Playlist[]
  queue?: Video[]
}

defineProps<Props>()
</script>

<template>
  <Head :title="item.name" />

  <Page>
    <PageBody>
      <PageFeature
        :title="item.name"
        :description="item.summary"
      />
    </PageBody>
  </Page>
</template>
