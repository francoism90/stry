<script setup lang="ts">
import { edit, index } from '@/actions/App/Web/Tags/Controllers/TagController'
import AppHeader from '@/components/Ui/AppHeader.vue'
import VideoFilterBar from '@/components/Videos/VideoFilterBar.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import { useEcho } from '@/composables/echo'
import type { Tag, VideoCollection, VideoFilters } from '@/types'
import { Head, InfiniteScroll, router } from '@inertiajs/vue3'
import type { NavigationMenuItem, SelectMenuItem } from '@nuxt/ui'

const props = defineProps<{
  tag: Tag
  items: VideoCollection
  filters: VideoFilters
  sorters: SelectMenuItem[]
  scopes: SelectMenuItem[]
  sort?: string
}>()

const links: NavigationMenuItem[] = [
  {
    label: 'Edit tag',
    icon: 'i-lucide-eye',
    to: edit.url(props.tag.id),
  },
]

const { privateChannel } = useEcho()

privateChannel(`tags.${props.tag.id}`)
  .listen('.tag.updated', () => router.reload({ only: ['tag'] }))
  .listen('.tag.deleted', () => router.visit(index.url()))
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

        <UDashboardToolbar>
          <template #left>
            <VideoFilterBar
              :results="Boolean(items?.data?.length)"
              :sorters="sorters"
              :scopes="scopes"
              :sort="sort"
              :filters="filters"
            />
          </template>
        </UDashboardToolbar>

        <InfiniteScroll
          data="items"
          items-element="#infinite-items"
          :buffer="200"
        >
          <VideoList
            id="infinite-items"
            :items="items?.data"
          />
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
