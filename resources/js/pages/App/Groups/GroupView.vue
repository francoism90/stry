<script setup lang="ts">
import { edit, index } from '@/actions/App/Web/Groups/Controllers/GroupController'
import AppHeader from '@/components/Ui/AppHeader.vue'
import VideoFilters from '@/components/Videos/VideoFilters.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import type { Group, VideoCollection } from '@/types'
import { Head, InfiniteScroll, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { NavigationMenuItem, SelectMenuItem } from '@nuxt/ui'

const props = defineProps<{
  group: Group
  items: VideoCollection
  orders: SelectMenuItem[]
  order: string
}>()

const links: NavigationMenuItem[] = [
  {
    label: 'Edit collection',
    icon: 'i-lucide-pencil',
    to: edit.url(props.group.id),
  },
]

useEcho<Group>(`groups.${props.group.id}`, '.group.updated', () => router.reload({ only: ['group'] }))
useEcho<Group>(`groups.${props.group.id}`, '.group.trashed', () => router.visit(index.url()))
</script>

<template>
  <Head :title="group.title" />

  <UDashboardPanel id="collection">
    <template #header>
      <AppHeader />
    </template>

    <template #body>
      <UPage>
        <UPageHeader
          :title="group.title"
          :description="`${group.videos ?? 0} videos`"
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
