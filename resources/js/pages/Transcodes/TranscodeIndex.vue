<script setup lang="ts">
import TranscodeDeleteModal from '@/components/Transcodes/TranscodeDeleteModal.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import ResourceLayout from '@/layouts/App/ResourceLayout.vue'
import type { TranscodeCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'

defineProps<{
  items: TranscodeCollection
}>()

defineOptions({
  layout: [AppLayout, ResourceLayout],
})
</script>

<template>
  <Head title="Transcodes" />

  <UPage>
    <UPageHeader
      title="Transcodes"
      description="Manage transcodes across all of your videos."
    />

    <UPageBody>
      <InfiniteScroll
        data="items"
        items-element="#infinite-items"
        :buffer="200"
      >
        <div id="infinite-items">
          <UEmpty
            v-if="!items?.data?.length"
            icon="i-lucide-cpu"
            title="No transcodes"
            description="Transcodes will appear here once videos are being processed."
          />

          <UPageList
            v-else
            divide
          >
            <UPageCard
              v-for="item in items?.data"
              :key="item.id"
              variant="naked"
              class="py-4 first:pt-0 last:pb-0"
            >
              <div class="flex items-center justify-between">
                <UUser
                  :name="item.resource?.label ?? item.id"
                  :description="`${item.state.label} · ${item.file_size}`"
                  :avatar="{
                    alt: item.resource?.label ?? item.id,
                    loading: 'lazy',
                    decoding: 'async',
                    class: 'rounded-sm size-12 me-1',
                  }"
                />

                <div class="z-10 flex items-center gap-2">
                  <TranscodeDeleteModal :item="item" />
                </div>
              </div>
            </UPageCard>
          </UPageList>
        </div>
      </InfiniteScroll>
    </UPageBody>
  </UPage>
</template>
