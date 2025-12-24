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
  <UDashboardPanel id="feed">
    <template #header>
      <UDashboardNavbar :ui="{ root: 'h-24 gap-3 border-0', left: 'w-full' }">
        <template #left>
          <UInput
            color="neutral"
            variant="soft"
            placeholder="Search..."
            icon="i-lucide-search"
            size="xl"
          />
        </template>

        <template #right>
          <UButton
            icon="i-lucide-settings"
            variant="soft"
            size="xl"
            :ui="{ base: 'p-3', leadingIcon: 'size-4' }"
          />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <UPage>
        <InfiniteScroll data="items">
          <VideoList :items="items" />
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
