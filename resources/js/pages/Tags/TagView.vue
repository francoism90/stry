<script setup lang="ts">
import { index, show } from '@/actions/Modules/Web/Tags/Controllers/TagController'
import TagEditModal from '@/components/Tags/TagEditModal.vue'
import AmbientWash from '@/components/Ui/AmbientWash.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import VideoListSkeleton from '@/components/Videos/VideoListSkeleton.vue'
import { tagIcon } from '@/composables/tags'
import ResourceLayout from '@/layouts/App/ResourceLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { OptionItem, QueryFilter, QueryValue, Tag, VideoCollection } from '@/types'
import { Head, InfiniteScroll, router, setLayoutProps } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { ButtonProps } from '@nuxt/ui'
import { computed, ref, watchEffect } from 'vue'

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
  layout: [AppLayout, ResourceLayout],
})

const isEditModalOpen = ref(false)
const itemBody = ref()

const links = computed<ButtonProps[]>(() => [
  {
    label: 'Edit',
    icon: 'i-lucide-pencil',
    onClick: () => (isEditModalOpen.value = true),
  },
])

const headline = computed<ButtonProps | undefined>(() =>
  props.tag.type
    ? {
        label: props.tag.category,
        icon: tagIcon(props.tag.type),
        to: index.url({ query: { filter: { scope: props.tag.type } } }),
      }
    : undefined,
)

const chips = computed<ButtonProps[]>(() =>
  (props.tag.related ?? []).map((related) => ({
    label: related.name,
    icon: tagIcon(related.type),
    to: show.url(related.id),
    'aria-label': `${related.category}: ${related.name}`,
  })),
)

const videoCount = computed(() => props.tag.videos ?? 0)

watchEffect(() => {
  setLayoutProps({
    id: 'tags.show',
    title: props.tag.name,
    description: `${Intl.NumberFormat().format(videoCount.value)} ${videoCount.value === 1 ? 'video' : 'videos'}`,
    headline: headline.value,
    links: links.value,
    chips: chips.value,
    scopes: props.scopes,
    sorters: props.sorters,
    filter: props.filter,
    sort: props.sort,
    query: props.query,
    fluid: true,
  })
})

useEcho(`tags.${props.tag.id}`, '.tag.updated', () => router.reload({ only: ['tag', 'items'], reset: ['items'] }))
useEcho(`tags.${props.tag.id}`, '.tag.deleted', () => router.visit(index.url()))
</script>

<template>
  <Head :title="tag.name" />

  <AmbientWash :images="typeof tag.thumb === 'string' ? [tag.thumb] : []" />

  <UPage>
    <TagEditModal
      v-model:open="isEditModalOpen"
      :item="tag"
      :trigger="false"
    />

    <InfiniteScroll
      data="items"
      :items-element="() => itemBody?.$el"
      :buffer="200"
    >
      <VideoList
        ref="itemBody"
        :items="items?.data"
      />

      <template #previous />

      <template #loading>
        <VideoListSkeleton class="mt-10" />
      </template>
    </InfiniteScroll>
  </UPage>
</template>
