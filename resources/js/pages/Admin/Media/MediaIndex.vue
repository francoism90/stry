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

  <UDashboardPanel id="Media">
    <template #header>
      <UDashboardNavbar title="Media">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <UPage>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-1.5"></div>

        <InfiniteScroll data="items">
          <UPageList divide>
            <UPageCard
              v-for="item in items?.data"
              :key="item.id"
              variant="ghost"
            >
              <UUser
                :name="item.name"
                :description="`${item.mime_type} • ${item.file_size}`"
                :avatar="{
                  alt: item.name,
                  class: 'rounded-sm size-12 me-1',
                }"
              />
            </UPageCard>
          </UPageList>
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
