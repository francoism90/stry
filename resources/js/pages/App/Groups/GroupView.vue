<script setup lang="ts">
import { edit, index } from '@/actions/App/Web/Groups/Controllers/GroupController'
import AppHeader from '@/components/Ui/AppHeader.vue'
import VideoFilterBar from '@/components/Videos/VideoFilterBar.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import { useEcho } from '@/composables/echo'
import type { Group, VideoCollection, VideoFilters } from '@/types'
import { Head, InfiniteScroll, router } from '@inertiajs/vue3'
import type { NavigationMenuItem, SelectMenuItem } from '@nuxt/ui'
import { computed } from 'vue'

const props = defineProps<{
  group: Group
  items: VideoCollection
  filters: VideoFilters
  sorters: SelectMenuItem[]
  sort?: string | null
}>()

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
          :description="`${Intl.NumberFormat().format(group.videos ?? 0)} videos`"
          :links="links"
        />

        <UDashboardToolbar>
          <template #left>
            <VideoFilterBar
              :results="Boolean(items?.data?.length)"
              :sorters="sorters"
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
