<script setup lang="ts">
import TranscodeList from '@/components/Transcodes/TranscodeList.vue'
import ContentLayout from '@/layouts/App/ContentLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { OptionItem, QueryFilter, QueryValue, TranscodeCollection } from '@/types'
import { Head, InfiniteScroll, setLayoutProps } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps<{
  items: TranscodeCollection
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
  id: 'transcodes.index',
  scopes: props.scopes,
  sorters: props.sorters,
  filter: props.filter,
  sort: props.sort,
  query: props.query,
})

const itemBody = ref()
</script>

<template>
  <Head title="Transcodes" />

  <UPage>
    <InfiniteScroll
      data="items"
      :items-element="() => itemBody?.$el"
    >
      <TranscodeList
        ref="itemBody"
        :items="items?.data"
      />
    </InfiniteScroll>
  </UPage>
</template>
