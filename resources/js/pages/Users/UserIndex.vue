<script setup lang="ts">
import UserList from '@/components/Users/UserList.vue'
import ContentLayout from '@/layouts/App/ContentLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { OptionItem, QueryFilter, QueryValue, UserCollection } from '@/types'
import { Head, InfiniteScroll, setLayoutProps } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps<{
  items: UserCollection
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
  id: 'users.index',
  scopes: props.scopes,
  sorters: props.sorters,
  filter: props.filter,
  sort: props.sort,
  query: props.query,
})

const itemBody = ref()
</script>

<template>
  <Head title="Users" />

  <UPage>
    <InfiniteScroll
      data="items"
      :items-element="() => itemBody?.$el"
      :buffer="200"
    >
      <UserList
        ref="itemBody"
        :items="items?.data"
      />
    </InfiniteScroll>
  </UPage>
</template>
