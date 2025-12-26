<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { PlaylistCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'

defineProps<{
  items: PlaylistCollection
}>()

defineOptions({ layout: DashboardLayout })
</script>

<template>
  <Head title="Playlists" />

  <UDashboardPanel id="playlists">
    <template #header>
      <UDashboardNavbar title="Playlists">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <UPage>
        <InfiniteScroll
          data="items"
          items-element="#playlist-list"
          start-element="#playlist-header"
        >
          <UPageList
            id="playlist-list"
            divide
          >
            <UPageCard
              v-for="item in items?.data"
              :key="item.id"
              variant="naked"
              class="py-4 first:pt-0 last:pb-0"
            >
              <UUser
                :name="item.id"
                :description="`${item.percent}% • ${item.state}`"
                :avatar="{
                  alt: item.id,
                  class: 'rounded-sm size-14 me-1',
                }"
              />
            </UPageCard>
          </UPageList>
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
