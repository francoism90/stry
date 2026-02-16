<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Media/Controllers/MediaController'
import MediaDeleteModal from '@/components/Media/MediaDeleteModal.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { MediaCollection } from '@/types'
import { Head, InfiniteScroll, usePoll } from '@inertiajs/vue3'

defineProps<{
  items: MediaCollection
}>()

defineOptions({ layout: DashboardLayout })

usePoll(30000, {
  only: ['items'],
  reset: ['items'],
})
</script>

<template>
  <Head title="Media" />

  <UDashboardPanel id="transcodes">
    <template #header>
      <UDashboardNavbar title="Media">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>

        <template #right>
          <div class="flex items-center gap-2">
            <MediaCreateModal />
          </div>
        </template>
      </UDashboardNavbar>

      <UDashboardToolbar
        id="transcode-header"
        class="min-h-16"
      />
    </template>

    <template #body>
      <InfiniteScroll
        data="items"
        start-element="#transcode-header"
        items-element="#transcode-list"
        :buffer="200"
      >
        <UPageList
          id="transcode-list"
          divide
        >
          <UPageCard
            v-for="item in items?.data"
            :key="item.id"
            :to="edit.url(item.id)"
            variant="naked"
            class="py-4 first:pt-0 last:pb-0"
          >
            <div class="flex items-center justify-between">
              <UUser
                :name="item.id"
                :description="`${item.file_size} • ${item.mime_type}`"
                :avatar="{
                  alt: item.id,
                  class: 'rounded-sm size-14 me-1',
                }"
              />

              <div class="z-10 flex items-center gap-2">
                <MediaDeleteModal :item="item" />
              </div>
            </div>
          </UPageCard>
        </UPageList>
      </InfiniteScroll>
    </template>
  </UDashboardPanel>
</template>
