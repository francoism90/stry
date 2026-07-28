<script setup lang="ts">
import TagList from '@/components/Tags/TagList.vue'
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import FilterBar from '@/components/Ui/FilterBar.vue'
import type { TagCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'

defineProps<{
  items: TagCollection
}>()
</script>

<template>
  <Head title="Tags" />

  <UDashboardPanel id="tags">
    <template #header>
      <AppHeader>
        <FilterBar
          placeholder="Search tags"
          :results="Boolean(items?.data?.length)"
        />
      </AppHeader>
    </template>

    <template #body>
      <UPage>
        <InfiniteScroll
          data="items"
          items-element="#infinite-items"
          :buffer="200"
        >
          <TagList
            id="infinite-items"
            :items="items?.data"
          />
        </InfiniteScroll>
      </UPage>
    </template>

    <template #footer>
      <AppFooter />
    </template>
  </UDashboardPanel>
</template>
