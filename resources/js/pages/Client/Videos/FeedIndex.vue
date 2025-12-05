<script setup lang="ts">
import VideoList from '@/components/Videos/VideoList.vue'
import type { VideoCollection } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { TabsItem } from '@nuxt/ui'
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
</script>

<template>
  <Head title="Home" />

  <UPage>
    <UTabs
      v-model="form.list"
      variant="link"
      class="w-full"
      :ui="{ trigger: 'grow py-2' }"
      :content="false"
      :items="lists"
      @update:modelValue="onSubmit"
    />

    <UPageBody class="mt-4 space-y-6">
      <VideoList :items="items" />
    </UPageBody>
  </UPage>
</template>
