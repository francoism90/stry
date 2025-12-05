<script setup lang="ts">
import VideoItems from '@/components/Library/VideoItems.vue'
import type { TagCollection } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { TabsItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: TagCollection
  type: string | number | undefined
  types: TabsItem[]
}>()

const form = useForm('get', '', {
  type: props.type,
  page: 1,
})

const onSubmit = () => {
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'search', 'type'],
    reset: ['items'],
  })
}

watchDebounced(
  () => form.type,
  () => onSubmit(),
  { debounce: 100, maxWait: 1000 },
)
</script>

<template>
  <Head title="Home" />

  <UPage>
    <UTabs
      v-model="form.type"
      :content="false"
      :items="types"
      variant="link"
      class="w-full"
      :ui="{
        trigger: 'grow py-2',
      }"
    />

    <VideoItems :items="items" />
  </UPage>
</template>
