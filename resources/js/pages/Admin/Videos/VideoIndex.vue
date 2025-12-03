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
              v-for="(item, index) in items.data"
              :key="index"
              :to="edit.url({ video: item.id })"
              variant="ghost"
            >
              <UUser
                :name="item.name"
                :avatar="{
                  alt: item.name,
                  src: item.thumb,
                  loading: 'lazy',
                  decoding: 'async',
                  class: 'rounded-sm size-12 me-1',
                }"
              >
                <template #description>
                  <div class="flex flex-col sm:flex-row sm:items-center sm:gap-2">
                    <span>{{ item.episode ? `Episode: ${item.episode}` : 'N/A' }}</span>
                    <span v-if="item.part">| Part: {{ item.part }}</span>
                    <span v-if="item.captioned">| Captioned</span>
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
