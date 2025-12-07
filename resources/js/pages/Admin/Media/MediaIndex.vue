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
                :avatar="{
                  alt: item.name,
                  class: 'rounded-sm size-12 me-1',
                }"
              >
                <template #description>
                  <div class="flex flex-col sm:flex-row sm:items-center sm:gap-2">
                    <span>{{ item.file_size ?? 'N/A' }}</span>
                  </div>
                </template>
              </UUser>
            </UPageCard>
          </UPageList>
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
