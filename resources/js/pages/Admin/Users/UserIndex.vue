<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { UserCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: UserCollection
  search: string | null
}>()

defineOptions({ layout: DashboardLayout })

const form = useForm('get', '', {
  search: props.search,
  page: 1,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items'],
    reset: ['items'],
  })

watchDebounced(
  () => form.search,
  () => onSubmit(),
  { debounce: 300, maxWait: 1000 },
)
</script>

<template>
  <Head title="Users" />

  <UDashboardPanel id="users">
    <template #header>
      <UDashboardNavbar title="Users">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>

        <template #right>
          <!-- <CustomersAddModal /> -->
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <UPage>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-1.5"></div>

        <InfiniteScroll data="items">
          <UPageList divide>
            <UPageCard
              v-for="(item, index) in items.data"
              :key="index"
              variant="ghost"
            >
              <UUser
                :name="item.name"
                :avatar="{
                  alt: item.name,
                  loading: 'lazy',
                  decoding: 'async',
                  class: 'rounded-sm size-12 me-1',
                }"
              />
            </UPageCard>
          </UPageList>
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
