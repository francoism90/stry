<script setup lang="ts">
import { index } from '@/actions/App/Web/Groups/Controllers/GroupController'
import GroupEditModal from '@/components/Groups/GroupEditModal.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import { useEcho } from '@/composables/echo'
import ContentLayout from '@/layouts/App/ContentLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { Group, OptionItem, QueryFilter, QueryValue, VideoCollection } from '@/types'
import { Head, InfiniteScroll, router, setLayoutProps } from '@inertiajs/vue3'
import type { ButtonProps } from '@nuxt/ui'
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

const isEditModalOpen = ref(false)
const itemBody = ref()

const links = computed<ButtonProps[]>(() => [
  {
    label: 'Edit collection',
    icon: 'i-lucide-pencil',
    onClick: () => (isEditModalOpen.value = true),
    disabled: props.group.type !== 'custom',
    class: props.group.type !== 'custom' ? 'hidden' : undefined,
  },
])

setLayoutProps({
  id: 'collections.show',
  title: props.group.title,
  description: `${Intl.NumberFormat().format(props.group.videos ?? 0)} videos`,
  links: links.value,
  scopes: props.scopes,
  sorters: props.sorters,
  filter: props.filter,
  sort: props.sort,
  query: props.query,
})

const { privateChannel } = useEcho()

privateChannel(`groups.${props.group.id}`)
  .listen('.group.updated', () => router.reload({ only: ['group'] }))
  .listen('.group.trashed', () => router.visit(index.url()))
</script>

<template>
  <Head :title="group.title" />

  <UPage>
    <GroupEditModal
      v-model:open="isEditModalOpen"
      :group="group"
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
