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
              :types="types"
              :type="type"
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
          :buffer="200"
        >
          <TagList :items="items?.data" />
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
