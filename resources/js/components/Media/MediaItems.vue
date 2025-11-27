<script setup lang="ts">
import { index } from '@/actions/App/Web/Media/Controllers/MediaController'
import type { Media, MediaCollection } from '@/types'
import type { TableColumn } from '@nuxt/ui'
import { ref } from 'vue'

const props = defineProps<{
  items: MediaCollection
}>()

const pagination = ref({
  pageIndex: props.items.meta.current_page,
  pageSize: props.items.meta.per_page,
})

const globalFilter = ref('')

const columns: TableColumn<Media>[] = [
  {
    accessorKey: 'id',
    header: '#',
    cell: ({ row }) => `#${row.getValue('id')}`,
  },
  {
    accessorKey: 'name',
    header: 'Name',
  },
]

const to = (page: number) => index.url({ mergeQuery: { page } })
</script>

<template>
  <div class="w-full space-y-4 pb-4">
    <div class="flex border-b border-accented px-4 py-3.5">
      <UInput
        v-model="globalFilter"
        class="max-w-sm"
        placeholder="Filter..."
      />
    </div>

    <UTable
      ref="table"
      v-model:pagination="pagination"
      v-model:global-filter="globalFilter"
      :data="items.data"
      :columns="columns"
      class="flex-1"
    />

    <div class="flex justify-end border-t border-default px-4 pt-4">
      <UPagination
        :page="items.meta.current_page"
        :total="items.meta.total"
        :items-per-page="items.meta.per_page"
        :sibling-count="1"
        :to="to"
      />
    </div>
  </div>
</template>
