<script setup lang="ts">
import AmbientWash from '@/components/Ui/AmbientWash.vue'
import TagList from '@/components/Tags/TagList.vue'
import TagListSkeleton from '@/components/Tags/TagListSkeleton.vue'
import { tagIcon } from '@/composables/tags'
import ContentLayout from '@/layouts/App/ContentLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { OptionItem, QueryFilter, QueryValue, TagCollection } from '@/types'
import { Head, InfiniteScroll, setLayoutProps } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

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
  scopes: props.scopes?.map((scope) =>
    Object.assign({}, scope, { icon: scope.value === 'all' ? undefined : tagIcon(String(scope.value)) }),
  ),
  sorters: props.sorters,
  filter: props.filter,
  sort: props.sort,
  query: props.query,
})

const itemBody = ref()

const washImages = computed(() =>
  (props.items?.data ?? [])
    .map((item) => item.thumb)
    .filter((thumb): thumb is string => typeof thumb === 'string')
    .slice(0, 1),
)
</script>

<template>
  <Head title="Tags" />

  <AmbientWash :images="washImages" />

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

      <template #loading>
        <TagListSkeleton class="mt-4" />
      </template>
    </InfiniteScroll>
  </UPage>
</template>
