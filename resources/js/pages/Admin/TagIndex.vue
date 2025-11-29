<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { TagCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: TagCollection
  type: string | null
  types: SelectMenuItem[]
}>()

defineOptions({ layout: DashboardLayout })

const form = useForm('get', '', {
  type: props.type,
  page: 1,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'type'],
    reset: ['items'],
  })
</script>

<template>
  <Head title="Library" />

  <UDashboardPanel id="tags">
    <template #header>
      <UDashboardNavbar title="Tags">
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
        <div class="flex flex-wrap items-center justify-between gap-1.5">
          <USelect
            v-model="form.type"
            :items="types"
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
              v-for="(item, index) in items.data"
              :key="index"
              variant="ghost"
            >
              <UUser
                class="px-0"
                size="xl"
                :name="item.name"
                :avatar="{
                  alt: item.name,
                  class: 'rounded-sm size-12',
                }"
              >
                <template #description>
                  <div class="flex flex-col sm:flex-row sm:items-center sm:gap-2">
                    <span>{{ item.videos }} videos</span>
                  </div>
                </template>
              </UUser>
            </UPageCard>
          </UPageList>
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
