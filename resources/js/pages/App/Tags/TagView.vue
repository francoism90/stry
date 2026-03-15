<script setup lang="ts">
import { edit, index } from '@/actions/App/Web/Tags/Controllers/TagController'
import AppHeader from '@/components/Ui/AppHeader.vue'
import VideoFilters from '@/components/Videos/VideoFilters.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import type { Tag, VideoCollection } from '@/types'
import { Head, InfiniteScroll, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { NavigationMenuItem, SelectMenuItem } from '@nuxt/ui'

const props = defineProps<{
  tag: Tag
  items: VideoCollection
  orders: SelectMenuItem[]
  order: string
}>()

const links: NavigationMenuItem[] = [
  {
    label: 'Edit tag',
    icon: 'i-lucide-eye',
    to: edit.url(props.tag.id),
  },
]

useEcho<Tag>(`tags.${props.tag.id}`, '.tag.updated', () => router.reload({ only: ['tag'] }))
useEcho<Tag>(`tags.${props.tag.id}`, '.tag.deleted', () => router.visit(index.url()))
</script>

<template>
  <Head :title="tag.name" />

  <UDashboardPanel id="tag">
    <template #header>
      <AppHeader />
    </template>

    <template #body>
      <UPage>
        <UPageHeader
          :title="tag.name"
          :description="tag.category"
          :links="links"
        />

        <VideoFilters
          :orders="orders"
          :order="order"
        />

        <InfiniteScroll
          data="items"
          :buffer="200"
        >
          <VideoList :items="items?.data" />
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
