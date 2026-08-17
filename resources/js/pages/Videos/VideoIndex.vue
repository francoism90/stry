<script setup lang="ts">
import VideoCard from '@/components/Videos/VideoCard.vue'
import ContentLayout from '@/layouts/App/ContentLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { VideoCollection } from '@/types'
import { Head, InfiniteScroll, setLayoutProps } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps<{
  items: VideoCollection
}>()

defineOptions({
  layout: [AppLayout, ContentLayout],
})

setLayoutProps({ id: 'videos.index', title: 'Videos' })

const itemBody = ref()
</script>

<template>
  <Head title="Videos" />

  <UPage>
    <InfiniteScroll
      data="items"
      :items-element="() => itemBody?.$el"
    >
      <UBlogPosts
        ref="itemBody"
        class="grid grid-cols-1 gap-4 gap-y-6 sm:grid-cols-3 lg:gap-y-8 xl:grid-cols-4"
      >
        <VideoCard
          v-for="(item, index) in items?.data ?? []"
          :key="item.id"
          :item="item"
          :index="index"
        />
      </UBlogPosts>
    </InfiniteScroll>
  </UPage>
</template>
