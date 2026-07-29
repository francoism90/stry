<script setup lang="ts">
import TagList from '@/components/Tags/TagList.vue'
import ContentLayout from '@/layouts/App/ContentLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { TagCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'

defineProps<{
  items: TagCollection
}>()

defineOptions({
  layout: [
    [AppLayout, { title: 'Tags' }],
    [ContentLayout, { id: 'tags.index' }],
  ],
})
</script>

<template>
  <Head title="Tags" />

  <UPage>
    <InfiniteScroll
      data="items"
      items-element="#infinite-items"
      :buffer="200"
    >
      <TagList
        id="infinite-items"
        :items="items?.data"
      />
    </InfiniteScroll>
  </UPage>
</template>
