<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Videos/Controllers/VideoController'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { VideoCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: VideoCollection
  orders: SelectMenuItem[] | undefined
  search: string | undefined
  order: string | undefined
}>()

defineOptions({ layout: DashboardLayout })

const form = useForm('get', '', {
  search: props.search,
  order: props.order,
  page: 1,
})

const onSubmit = () => {
  form.submit({
    preserveState: 'errors',
    replace: true,
    only: ['items', 'search', 'order'],
    reset: ['items'],
  })
}

watchDebounced(
  () => form.search,
  () => onSubmit(),
  { debounce: 300, maxWait: 1000 },
)
</script>

<template>
  <Head title="Videos" />

  <UDashboardPanel id="videos">
    <template #header>
      <UDashboardNavbar title="Videos">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>
      </UDashboardNavbar>

      <UDashboardToolbar
        id="video-header"
        class="min-h-20"
      >
        <template #left>
          <UFormField :error="form.errors.order">
            <USelect
              v-model="form.order"
              :items="orders"
              :ui="{ content: 'min-w-36' }"
              label-key="label"
              value-key="value"
              @update:modelValue="onSubmit"
            />
          </UFormField>

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
          start-element="#video-header"
          items-element="#video-list"
        >
          <UPageList
            id="video-list"
            divide
          >
            <UPageCard
              v-for="item in items?.data"
              :key="item.id"
              :to="edit.url(item.id)"
              variant="naked"
              class="py-4 first:pt-0 last:pb-0"
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
