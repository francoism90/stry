<script setup lang="ts">
import TagCard from '@/components/Tag/TagCard.vue'
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
  <Head title="Tags" />

  <UDashboardPanel id="tags">
    <template #header>
      <UDashboardNavbar title="Tags">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>

        <template #right>
          <!-- <CustomersAddModal /> -->
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <InfiniteScroll
        data="items"
        items-element="#video-items"
        :buffer="200"
      >
        <UBlogPosts id="video-items">
          <TagCard
            v-for="item in items?.data"
            :key="item.id"
            :item="item"
          />
        </UBlogPosts>
      </InfiniteScroll>
    </template>
  </UDashboardPanel>
</template>
