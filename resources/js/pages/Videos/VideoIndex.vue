<script setup lang="ts">
import AmbientWash from '@/components/Ui/AmbientWash.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import VideoListSkeleton from '@/components/Videos/VideoListSkeleton.vue'
import { useWashImages } from '@/composables/ambient'
import ContentLayout from '@/layouts/App/ContentLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { OptionItem, QueryFilter, QueryValue, VideoCollection } from '@/types'
import { Head, InfiniteScroll, setLayoutProps } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps<{
  items: VideoCollection
  scopes?: OptionItem[]
  sorters?: OptionItem[]
  filter?: QueryFilter
  sort?: QueryValue
  query?: QueryValue
}>()

defineOptions({
  layout: [AppLayout, ContentLayout],
})

setLayoutProps({
  id: 'videos.index',
  scopes: props.scopes,
  sorters: props.sorters,
  filter: props.filter,
  sort: props.sort,
  query: props.query,
})

const itemBody = ref()

const washImages = useWashImages(() => (props.items?.data ?? []).map((item) => item.thumb), 3)
</script>

<template>
  <Head title="Videos" />

  <AmbientWash :images="washImages" />

  <UPage>
    <InfiniteScroll
      data="items"
      :items-element="() => itemBody?.$el"
      :buffer="200"
    >
      <VideoList
        ref="itemBody"
        :items="items?.data"
      />

      <template #previous />

      <template #loading>
        <VideoListSkeleton class="mt-10" />
      </template>
    </InfiniteScroll>
  </UPage>
</template>
