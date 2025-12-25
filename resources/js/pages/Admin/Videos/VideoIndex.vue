<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Videos/Controllers/VideoController'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { VideoCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: VideoCollection
  orders: SelectMenuItem[]
  search: string | undefined
  order: string | undefined
}>()

defineOptions({ layout: DashboardLayout })

const form = useForm('get', '', {
  order: props.order,
  page: 1,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'order'],
    reset: ['items'],
  })
</script>

<template>
  <Head title="Videos" />

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
      <UPage>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-1.5">
          <USelect
            v-model="form.order"
            :items="orders"
            :ui="{ content: 'min-w-36' }"
            label-key="label"
            value-key="value"
            placeholder="Filter by"
            @update:modelValue="onSubmit"
          />
        </div>

        <InfiniteScroll data="items">
          <UPageList divide>
            <UPageCard
              v-for="item in items?.data"
              :key="item.id"
              :to="edit.url(item.id)"
              variant="naked"
              class="py-4"
            >
              <UUser
                :name="item.title"
                :description="item.timestamp"
                :avatar="{
                  alt: item.name,
                  src: item.thumb,
                  loading: 'lazy',
                  decoding: 'async',
                  class: 'rounded-sm size-14 me-1',
                }"
              />
            </UPageCard>
          </UPageList>
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
