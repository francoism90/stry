<script setup lang="ts">
import GroupCard from '@/components/Groups/GroupCard.vue'
import ContentLayout from '@/layouts/App/ContentLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { GroupCollection, OptionItem, QueryFilter, QueryValue } from '@/types'
import { Head, InfiniteScroll, setLayoutProps } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps<{
  items: GroupCollection
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
  id: 'collections.index',
  title: 'Collections',
  scopes: props.scopes,
  sorters: props.sorters,
  filter: props.filter,
  sort: props.sort,
  query: props.query,
})

const itemBody = ref()
</script>

<template>
  <Head title="Collections" />

  <UPage>
    <InfiniteScroll
      data="items"
      :items-element="() => itemBody?.$el"
    >
      <UBlogPosts
        ref="itemBody"
        class="grid grid-cols-1 gap-4 gap-y-6 sm:grid-cols-3 lg:gap-y-8 xl:grid-cols-4"
      >
        <GroupCard
          v-for="item in items?.data ?? []"
          :key="item.id"
          :item="item"
        />
      </UBlogPosts>
    </InfiniteScroll>
  </UPage>
</template>
