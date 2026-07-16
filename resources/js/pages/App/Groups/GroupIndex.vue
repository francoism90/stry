<script setup lang="ts">
import GroupCreateModal from '@/components/Groups/GroupCreateModal.vue'
import GroupFilterBar from '@/components/Groups/GroupFilterBar.vue'
import GroupList from '@/components/Groups/GroupList.vue'
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import type { GroupCollection } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

defineProps<{
  items: GroupCollection
  sorters: SelectMenuItem[]
  sort?: string | null
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
            <GroupFilterBar
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
          <GroupList
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
