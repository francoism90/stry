<script setup lang="ts">
import { index } from '@/actions/App/Web/Tags/Controllers/TagController'
import TagEditModal from '@/components/Tags/TagEditModal.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import { useEcho } from '@/composables/echo'
import ContentLayout from '@/layouts/App/ContentLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { OptionItem, QueryFilter, QueryValue, Tag, VideoCollection } from '@/types'
import { Head, InfiniteScroll, router, setLayoutProps } from '@inertiajs/vue3'
import type { ButtonProps } from '@nuxt/ui'
import { computed, ref } from 'vue'

const props = defineProps<{
  tag: Tag
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
  id: 'tags.show',
  title: props.tag.name,
  scopes: props.scopes,
  sorters: props.sorters,
  filter: props.filter,
  sort: props.sort,
  query: props.query,
})

const isEditModalOpen = ref(false)

const links = computed<ButtonProps[]>(() => [
  {
    label: 'Edit tag',
    icon: 'i-lucide-pencil',
    onClick: () => (isEditModalOpen.value = true),
  },
])

const { privateChannel } = useEcho()

privateChannel(`tags.${props.tag.id}`)
  .listen('.tag.updated', () => router.reload({ only: ['tag'] }))
  .listen('.tag.deleted', () => router.visit(index.url()))

const itemBody = ref()
</script>

<template>
  <Head :title="tag.name" />

  <UPage>
    <UPageHeader
      :title="tag.name"
      :description="`${Intl.NumberFormat().format(tag.videos ?? 0)} videos`"
      :links="links"
    />

    <TagEditModal
      v-model:open="isEditModalOpen"
      :item="tag"
      :trigger="false"
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
