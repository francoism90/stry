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
    only: ['items', 'search'],
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
      </UDashboardNavbar>

      <UDashboardToolbar
        id="user-header"
        class="min-h-20"
      >
        <template #left>
          <UFormField :error="form.errors.search">
            <UInput
              v-model="form.search"
              :model-modifiers="{ string: true, trim: true }"
              color="neutral"
              placeholder="Search..."
              icon="i-lucide-search"
            />
          </UFormField>
        </template>
      </UDashboardToolbar>
    </template>

    <template #body>
      <UPage>
        <InfiniteScroll
          data="items"
          start-element="#user-header"
          items-element="#user-list"
          :buffer="200"
        >
          <UPageList
            id="user-list"
            divide
          >
            <UPageCard
              v-for="item in items?.data"
              :key="item.id"
              variant="naked"
              class="py-4 first:pt-0 last:pb-0"
            >
              <UUser
                :name="item.name"
                :description="`${item.created_at}`"
                :avatar="{
                  alt: item.name,
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
