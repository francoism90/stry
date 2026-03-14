<script setup lang="ts">
import GroupFilters from '@/components/Groups/GroupFilters.vue'
import GroupList from '@/components/Groups/GroupList.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import type { GroupCollection } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

defineProps<{
  items: GroupCollection
  types: SelectMenuItem[]
  type: string | null
}>()
</script>

<template>
  <UDashboardPanel id="collections">
    <template #header>
      <AppHeader />
    </template>

    <template #body>
      <UPage>
        <GroupFilters
          :types="types"
          :type="type ?? undefined"
        />

        <InfiniteScroll
          data="items"
          :buffer="200"
        >
          <GroupList :items="items?.data" />
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
