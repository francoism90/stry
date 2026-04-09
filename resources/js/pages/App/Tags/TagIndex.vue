<script setup lang="ts">
import TagCreateModal from '@/components/Tags/TagCreateModal.vue'
import TagFilters from '@/components/Tags/TagFilters.vue'
import TagList from '@/components/Tags/TagList.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import { useAuth } from '@/composables/auth'
import type { TagCollection } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

defineProps<{
  items: TagCollection
  types: SelectMenuItem[]
  type?: string
  sorters: SelectMenuItem[]
  sort?: string
}>()

const { hasAnyRole } = useAuth()
</script>

<template>
  <UDashboardPanel id="tags">
    <template #header>
      <AppHeader />
    </template>

    <template #body>
      <UPage>
        <UDashboardToolbar>
          <template #left>
            <TagFilters
              :results="Boolean(items?.data?.length)"
              :types="types"
              :type="type"
              :sorters="sorters"
              :sort="sort"
            />
          </template>

          <template
            v-if="hasAnyRole(['admin', 'super-admin'])"
            #right
          >
            <TagCreateModal :types="types" />
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
              icon="i-lucide-tags"
              title="No tags"
            />

            <TagList
              v-else
              :items="items?.data"
            />
          </div>
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
