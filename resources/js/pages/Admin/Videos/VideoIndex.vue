<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Videos/Controllers/VideoController'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { VideoCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: VideoCollection
  sort: string | null
  sorters: SelectMenuItem[]
}>()

defineOptions({ layout: DashboardLayout })

const form = useForm('get', '', {
  sort: props.sort,
  page: 1,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'sort'],
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
            v-model="form.sort"
            :items="sorters"
            label-key="label"
            value-key="value"
            placeholder="Filter by"
            class="w-32 sm:w-36"
            @update:modelValue="onSubmit"
          />
        </div>

        <InfiniteScroll data="items">
          <UPageList divide>
            <UPageCard
              v-for="item in items?.data"
              :key="item.id"
              :to="edit.url(item.id)"
              variant="ghost"
            >
              <UUser
                :name="item.title"
                :description="item.timestamp"
                :avatar="{
                  alt: item.name,
                  src: item.thumb,
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
