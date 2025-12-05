<script setup lang="ts">
import VideoItems from '@/components/Library/VideoItems.vue'
import type { VideoCollection } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { TabsItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: VideoCollection
  list: string | number | undefined
  lists: TabsItem[]
}>()

const form = useForm('get', '', {
  list: props.list,
  page: 1,
})

const onSubmit = () => {
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'search', 'list'],
    reset: ['items'],
  })
}

watchDebounced(
  () => form.list,
  () => onSubmit(),
  { debounce: 100, maxWait: 1000 },
)
</script>

<template>
  <Head title="Home" />

  <UPage>
    <UTabs
      v-model="form.list"
      :content="false"
      :items="lists"
      variant="link"
      class="w-full"
      :ui="{
        trigger: 'grow py-2',
      }"
    />

    <UPageBody class="mt-4 space-y-6">
      <VideoItems :items="items" />
    </UPageBody>
  </UPage>
</template>
