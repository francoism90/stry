<script setup lang="ts">
import TagCard from '@/components/Tags/TagCard.vue'
import ContentLayout from '@/layouts/App/ContentLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { TagCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps<{
  items: TagCollection
}>()

defineOptions({
  layout: [
    [AppLayout, { title: 'Tags' }],
    [ContentLayout, { id: 'tags.index', title: 'Tags' }],
  ],
})

const itemBody = ref()
</script>

<template>
  <Head title="Tags" />

  <UPage>
    <InfiniteScroll
      data="items"
      :items-element="() => itemBody?.$el"
    >
      <UBlogPosts
        ref="itemBody"
        class="grid grid-cols-1 gap-4 gap-y-6 sm:grid-cols-3 lg:gap-y-8 xl:grid-cols-4"
      >
        <TagCard
          v-for="(item, index) in items?.data ?? []"
          :key="item.id"
          :item="item"
          :index="index"
        />
      </UBlogPosts>
    </InfiniteScroll>
  </UPage>
</template>
