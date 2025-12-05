<script setup lang="ts">
import VideoItems from '@/components/Library/VideoItems.vue'
import type { VideoCollection } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { SelectMenuItem, TabsItem } from '@nuxt/ui'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: VideoCollection
  sort: string | null
  sorters: SelectMenuItem[]
}>()

const tabs: TabsItem[] = [
  {
    label: 'Recommended',
  },
  {
    label: 'Recently Watched',
  },
  {
    label: 'Most Recent',
  },
]

const form = useForm('get', '', {
  sort: props.sort,
  page: 1,
})
</script>

<template>
  <Head title="Home" />

  <UPage>
    <UTabs
      :items="tabs"
      variant="link"
      :ui="{
        trigger: 'grow py-2',
      }"
    />

    <VideoItems :items="items" />
  </UPage>
</template>
