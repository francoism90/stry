<script setup lang="ts">
import { edit, index } from '@/actions/App/Web/Groups/Controllers/GroupController'
import VideoList from '@/components/Videos/VideoList.vue'
import { useEcho } from '@/composables/echo'
import ContentLayout from '@/layouts/App/ContentLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { Group, OptionItem, QueryFilter, QueryValue, VideoCollection } from '@/types'
import { Head, InfiniteScroll, router, setLayoutProps } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'
import { computed, ref } from 'vue'

const props = defineProps<{
  group: Group
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
  id: 'collections.show',
  title: props.group.title,
  scopes: props.scopes,
  sorters: props.sorters,
  filter: props.filter,
  sort: props.sort,
  query: props.query,
})

const links = computed<NavigationMenuItem[]>(() => [
  {
    label: 'Edit collection',
    icon: 'i-lucide-pencil',
    to: edit.url(props.group.id),
    disabled: props.group.type !== 'custom',
    class: props.group.type !== 'custom' ? 'hidden' : undefined,
  },
])

const { privateChannel } = useEcho()

privateChannel(`groups.${props.group.id}`)
  .listen('.group.updated', () => router.reload({ only: ['group'] }))
  .listen('.group.trashed', () => router.visit(index.url()))

const itemBody = ref()
</script>

<template>
  <Head :title="group.title" />

  <UPage>
    <UPageHeader
      :title="group.title"
      :description="`${Intl.NumberFormat().format(group.videos ?? 0)} videos`"
      :links="links"
    />

    <InfiniteScroll
      data="items"
      :items-element="() => itemBody?.$el"
    >
      <VideoList
        ref="itemBody"
        :items="items?.data"
      />
    </InfiniteScroll>
  </UPage>
</template>
