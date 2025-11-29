<script setup lang="ts">
import { index } from '@/actions/App/Admin/Videos/Controllers/VideoController'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { Video, VideoCollection } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { TableColumn } from '@nuxt/ui'
import type { Column, Row, SortingFn } from '@tanstack/vue-table'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'
import { h, ref, resolveComponent } from 'vue'

const props = defineProps<{
  items: VideoCollection
  sort: string | null
  desc: boolean | null
  search: string | null
}>()

defineOptions({ layout: DashboardLayout })

const UAvatar = resolveComponent('UAvatar')
const UButton = resolveComponent('UButton')

const form = useForm('get', '', {
  sort: props.sort,
  desc: props.desc,
  search: props.search,
  page: 1,
})

const sorting = ref([
  {
    id: 'name',
    desc: false,
  },
])

const pagination = ref({
  pageIndex: props.items.meta.current_page,
  pageSize: props.items.meta.per_page,
})

const myCustomSortingFn: SortingFn<Video> = (rowA: Row<Video>, rowB: Row<Video>, columnId: string) => {
  console.log('Custom sorting function called for column:', columnId)
  // console.log('Row A data:', rowA.original)
  // console.log('Row B data:', rowB.original)

  // form.sort = columnId
  // form.desc = false
  // onSubmit()

  return 0
}

const getHeader = (column: Column<Video>, label: string) => {
  const isSorted = column.getIsSorted()

  return h(UButton, {
    color: 'neutral',
    variant: 'ghost',
    label: label,
    icon: isSorted ? (isSorted === 'asc' ? 'i-lucide-arrow-up-narrow-wide' : 'i-lucide-arrow-down-wide-narrow') : 'i-lucide-arrow-up-down',
    class: '-mx-2.5',
    onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
  })
}

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'sort', 'desc', 'search'],
    reset: ['items'],
  })

const to = (page: number) => index.url({ mergeQuery: { ...form.data(), page } })

const columns: TableColumn<Video>[] = [
  {
    accessorKey: 'name',
    sortingFn: myCustomSortingFn,
    header: ({ column }) => getHeader(column, 'Name'),
    cell: ({ row }) => {
      return h('div', { class: 'flex items-center gap-3' }, [
        h(UAvatar, {
          src: row.original.thumb,
          size: 'lg',
          loading: 'lazy',
        }),
        h('div', undefined, [
          h('p', { class: 'font-medium text-highlighted' }, row.original.name),
          h('p', { class: '' }, `@${row.original.name}`),
        ]),
      ])
    },
  },
  {
    accessorKey: 'created_at',
    header: 'Created At',
  },
]

watchDebounced(
  () => form.search,
  () => onSubmit(),
  { debounce: 300, maxWait: 1000 },
)
</script>

<template>
  <Head title="Library" />

  <UDashboardPanel id="videos">
    <template #header>
      <UDashboardNavbar title="Videos">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>

        <template #right>
          <!-- <CustomersAddModal /> -->
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-1.5">
          <UInput
            v-model="form.search"
            type="search"
            name="search"
            class="max-w-sm"
            icon="i-lucide-search"
            placeholder="Filter..."
          />
        </div>

        <UTable
          ref="table"
          v-model:pagination="pagination"
          v-model:sorting="sorting"
          class="flex-1"
          :data="items.data || []"
          :columns="columns"
          :sibling-count="1"
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
          <div class="text-sm text-muted">{{ items.meta.to }} of {{ items.meta.total }} results</div>

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
  </UDashboardPanel>
</template>
