<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Users/Controllers/UserController'
import UserCreateModal from '@/components/Users/UserCreateModal.vue'
import UserDeleteModal from '@/components/Users/UserDeleteModal.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { UserCollection } from '@/types'
import { Head, InfiniteScroll, usePoll } from '@inertiajs/vue3'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: UserCollection
  search?: string | null
}>()

defineOptions({ layout: DashboardLayout })

usePoll(5000, {
  only: ['items'],
  reset: ['items'],
})

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

        <template #right>
          <UserCreateModal />
        </template>
      </UDashboardNavbar>

      <UDashboardToolbar
        id="user-header"
        class="min-h-16"
      >
        <template #left>
          <UFormField :error="form.errors.search">
            <UInput
              v-model="form.search"
              :model-modifiers="{ string: true, trim: true }"
              color="neutral"
              class="min-w-64"
              placeholder="Search..."
              icon="i-lucide-search"
            />
          </UFormField>
        </template>
      </UDashboardToolbar>
    </template>

    <template #body>
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
            :to="edit.url(item.id)"
            variant="naked"
            class="py-4 first:pt-0 last:pb-0"
          >
            <div class="flex items-center justify-between">
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

              <div class="z-10 flex items-center gap-2">
                <UButton
                  icon="i-lucide-pencil"
                  color="secondary"
                  variant="ghost"
                  size="sm"
                  :to="edit.url(item.id)"
                />

                <UserDeleteModal :item="item">
                  <UButton
                    icon="i-lucide-trash"
                    color="error"
                    variant="ghost"
                    size="sm"
                  />
                </UserDeleteModal>
              </div>
            </div>
          </UPageCard>
        </UPageList>
      </InfiniteScroll>
    </template>
  </UDashboardPanel>
</template>
