<script setup lang="ts">
import { index } from '@/actions/App/Web/Media/Controllers/MediaController'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { Media, MediaCollection } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { TableColumn } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'
import { ref } from 'vue'

const props = defineProps<{
  items: MediaCollection
  search?: string | null
}>()

defineOptions({ layout: DashboardLayout })

const form = useForm('get', '', {
  search: props.search || '',
})

const pagination = ref({
  pageIndex: props.items.meta.current_page,
  pageSize: props.items.meta.per_page,
})

const columns: TableColumn<Media>[] = [
  {
    accessorKey: 'file_name',
    header: 'File',
  },
  {
    accessorKey: 'file_size',
    header: 'Size',
  },
  {
    accessorKey: 'collection_name',
    header: 'Collection',
  },
  {
    accessorKey: 'mime_type',
    header: 'MIME Type',
  },
  {
    accessorKey: 'created_at',
    header: 'Created At',
  },
]

const onSubmit = () => {
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'search'],
    reset: ['items'],
  })
}

const to = (page: number) => index.url({ mergeQuery: { page, ...form.data() } })

watchDebounced(
  () => form.search,
  () => onSubmit(),
  { debounce: 300, maxWait: 1000 },
)
</script>

<template>
  <Head title="Library" />

  <UDashboardPanel id="media">
    <template #header>
      <UDashboardNavbar title="Media">
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
          :data="items.data || []"
          :columns="columns"
          :sibling-count="1"
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
