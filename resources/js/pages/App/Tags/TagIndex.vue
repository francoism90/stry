<script setup lang="ts">
import TagFilters from '@/components/Tags/TagFilters.vue'
import TagList from '@/components/Tags/TagList.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import type { TagCollection } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

defineProps<{
  items: TagCollection
  types: SelectMenuItem[]
  type: string
}>()
</script>

<template>
  <UDashboardPanel id="tags">
    <template #header>
      <AppHeader />

      <TagFilters
        :types="types"
        :type="type"
      />
    </template>

    <template #body>
      <UPage>
        <InfiniteScroll
          data="items"
          :buffer="200"
        >
          <TagList :items="items?.data" />
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
