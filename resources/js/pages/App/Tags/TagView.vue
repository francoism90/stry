<script setup lang="ts">
import AppHeader from '@/components/Ui/AppHeader.vue'
import VideoFilters from '@/components/Videos/VideoFilters.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import type { Tag, VideoCollection } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { SelectMenuItem } from '@nuxt/ui'

const props = defineProps<{
  tag: Tag
  items: VideoCollection
  orders: SelectMenuItem[]
  order: string
}>()

useEcho<Tag>(`tags.${props.tag.id}`, '.tag.updated', () => router.reload({ only: ['tag'] }))
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
