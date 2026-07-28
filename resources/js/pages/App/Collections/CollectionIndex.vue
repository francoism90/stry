<script setup lang="ts">
import CollectionList from '@/components/Collections/CollectionList.vue'
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import FilterBar from '@/components/Ui/FilterBar.vue'
import type { GroupCollection } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'

defineProps<{
  items: GroupCollection
}>()
</script>

<template>
  <UDashboardPanel id="collections">
    <template #header>
      <AppHeader>
        <FilterBar placeholder="Search collections" />
      </AppHeader>
    </template>

    <template #body>
      <UPage>
        <InfiniteScroll
          data="items"
          items-element="#infinite-items"
          :buffer="200"
        >
          <CollectionList
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
