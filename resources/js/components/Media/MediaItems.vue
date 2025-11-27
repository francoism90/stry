<script setup lang="ts">
import { index } from '@/actions/App/Web/Media/Controllers/MediaController'
import type { Media, MediaCollection } from '@/types'
import type { TableColumn } from '@nuxt/ui'
import { ref, useTemplateRef } from 'vue'

const props = defineProps<{
  items: MediaCollection
}>()

const table = useTemplateRef('table')

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
  <div class="flex flex-col gap-6">
    <div class="flex flex-wrap items-center justify-between gap-1.5">
      <UInput
        v-model="globalFilter"
        class="max-w-sm"
        icon="i-lucide-search"
        placeholder="Filter media..."
      />
    </div>

    <UTable
      ref="table"
      v-model:pagination="pagination"
      v-model:global-filter="globalFilter"
      :data="items.data || []"
      :columns="columns"
      class="shrink-0"
      :ui="{
        base: 'table-fixed border-separate border-spacing-0',
        thead: '[&>tr]:bg-elevated/50 [&>tr]:after:content-none',
        tbody: '[&>tr]:last:[&>td]:border-b-0',
        th: 'border-y border-default py-2 first:rounded-l-lg first:border-l last:rounded-r-lg last:border-r',
        td: 'border-b border-default',
        separator: 'h-0',
      }"
    />

    <div class="mt-auto flex items-center justify-between gap-3 border-t border-default pt-4">
      <div class="text-sm text-muted">{{ items.meta.total }} results</div>

      <div class="flex items-center gap-1.5">
        <UPagination
          :page="items.meta.current_page"
          :total="items.meta.total"
          :items-per-page="items.meta.per_page"
          :sibling-count="1"
          :to="to"
        />
      </div>
    </div>
  </div>
</template>
