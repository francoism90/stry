<script setup lang="ts">
import VideoItems from '@/components/Library/VideoItems.vue'
import type { VideoCollection } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { SelectMenuItem, TabsItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: VideoCollection
  sort: string | number | undefined
  sorters: SelectMenuItem[]
}>()

const tabs: TabsItem[] = [
  {
    label: 'Recommended',
    value: 'recommended',
  },
  {
    label: 'Recently Watched',
    value: 'watched',
  },
  {
    label: 'Most Recent',
    value: 'newest',
  },
]

const form = useForm('get', '', {
  sort: props.sort,
  page: 1,
})

const onSubmit = () => {
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'search', 'sort'],
    reset: ['items'],
  })
}

watchDebounced(
  () => form.sort,
  () => onSubmit(),
  { debounce: 100, maxWait: 1000 },
)
</script>

<template>
  <Head title="Home" />

  <UPage>
    <UTabs
      v-model="form.sort"
      :content="false"
      :items="tabs"
      variant="link"
      class="w-full"
      :ui="{
        trigger: 'grow py-2',
      }"
    />

    <VideoItems :items="items" />
  </UPage>
</template>
