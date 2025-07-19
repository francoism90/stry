<script setup lang="ts">
import Page from '@/components/Ui/Page.vue'
import PageBody from '@/components/Ui/PageBody.vue'
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
      {{ $page.props.auth.user?.permissions?.includes('edit videos') }}
    </PageBody>
  </Page>
</template>
