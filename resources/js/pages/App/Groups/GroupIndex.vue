<script setup lang="ts">
import GroupCreateModal from '@/components/Groups/GroupCreateModal.vue'
import GroupFilters from '@/components/Groups/GroupFilters.vue'
import GroupList from '@/components/Groups/GroupList.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import type { GroupCollection } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

defineProps<{
  items: GroupCollection
  sorters: SelectMenuItem[]
  sort?: string
}>()
</script>

<template>
  <UDashboardPanel id="collections">
    <template #header>
      <AppHeader />
    </template>

    <template #body>
      <UPage>
        <UDashboardToolbar>
          <template #left>
            <GroupFilters
              :results="Boolean(items?.data?.length)"
              :sorters="sorters"
              :sort="sort"
            />
          </template>

          <template #right>
            <GroupCreateModal />
          </template>
        </UDashboardToolbar>

        <InfiniteScroll
          data="items"
          items-element="#infinite-items"
          :buffer="200"
        >
          <div id="infinite-items">
            <UEmpty
              v-if="!items?.data?.length"
              icon="i-lucide-folder"
              title="No collections"
            />

            <GroupList
              v-else
              :items="items?.data"
            />
          </div>
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
