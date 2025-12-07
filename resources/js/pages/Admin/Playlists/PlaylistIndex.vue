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

        <template #right>
          <!-- <CustomersAddModal /> -->
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
                :name="item.id"
                :avatar="{
                  alt: item.id,
                  class: 'rounded-sm size-12 me-1',
                }"
              >
                <template #description>
                  <div class="flex flex-col sm:flex-row sm:items-center sm:gap-2">
                    <span>{{ item.percent ?? '0' }}%</span>
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
