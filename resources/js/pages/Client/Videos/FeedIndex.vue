<script setup lang="ts">
import VideoList from '@/components/Videos/VideoList.vue'
import type { VideoCollection } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'
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
  <UDashboardPanel id="app">
    <template #body>
      <UPage>
        <UTabs
          v-model="form.list"
          variant="link"
          class="w-full"
          :ui="{ trigger: 'grow py-3' }"
          :content="false"
          :items="lists"
          @update:modelValue="onSubmit"
        />

        <InfiniteScroll data="items">
          <VideoList :items="items" />
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
