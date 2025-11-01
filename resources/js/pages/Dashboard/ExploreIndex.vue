<script setup lang="ts">
import DashboardToolbar from '@/components/Dashboard/DashboardToolbar.vue'
import TagList from '@/components/Tag/TagList.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { TagCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'

interface Props {
  items: TagCollection
}

defineOptions({ layout: DashboardLayout })

defineProps<Props>()
</script>

<template>
  <Head title="Library" />

  <UPage>
    <UPageBody>
      <DashboardToolbar default-filter="genre" />

      <UContainer class="py-4">
        <InfiniteScroll
          items-element="#item-list"
          data="items"
          :buffer="200"
        >
          <TagList :items="items" />
        </InfiniteScroll>
      </UContainer>
    </UPageBody>
  </UPage>
</template>
