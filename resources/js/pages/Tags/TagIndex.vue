<script setup lang="ts">
import TagList from '@/components/Tags/TagList.vue'
import ContentLayout from '@/layouts/App/ContentLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { OptionItem, QueryFilter, QueryValue, TagCollection } from '@/types'
import { Head, InfiniteScroll, setLayoutProps } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps<{
  items: TagCollection
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
  id: 'tags.index',
  scopes: props.scopes,
  sorters: props.sorters,
  filter: props.filter,
  sort: props.sort,
  query: props.query,
})

const itemBody = ref()
</script>

<template>
  <Head title="Tags" />

  <UPage>
    <InfiniteScroll
      data="items"
      :items-element="() => itemBody?.$el"
      :buffer="200"
    >
      <TagList
        ref="itemBody"
        :items="items?.data"
      />
    </InfiniteScroll>
  </UPage>
</template>
