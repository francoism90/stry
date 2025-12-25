<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { MediaCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'

defineProps<{
  items: MediaCollection
}>()

defineOptions({ layout: DashboardLayout })
</script>

<template>
  <Head title="Videos" />

  <UDashboardPanel id="media">
    <template #header>
      <UDashboardNavbar title="Media">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <UPage>
        <InfiniteScroll
          data="items"
          items-element="#media-list"
          start-element="#media-header"
          :buffer="200"
        >
          <UPageList
            id="media-list"
            divide
          >
            <UPageCard
              v-for="item in items?.data"
              :key="item.id"
              variant="naked"
              class="py-4 first:pt-0 last:pb-0"
            >
              <UUser
                :name="item.name"
                :description="`${item.mime_type} • ${item.file_size}`"
                :avatar="{
                  alt: item.name,
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
