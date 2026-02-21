<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Videos/Controllers/VideoController'
import VideoController from '@/actions/App/Client/Videos/Controllers/VideoController'
import VideoDeleteModal from '@/components/Videos/VideoDeleteModal.vue'
import VideoImportModal from '@/components/Videos/VideoImportModal.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { VideoCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: VideoCollection
  orders: SelectMenuItem[]
  order?: string | undefined
  search?: string | null
}>()

defineOptions({ layout: DashboardLayout })

const form = useForm('get', '', {
  search: props.search,
  order: props.order,
  page: 1,
})

const onSubmit = () => {
  form.submit({
    preserveState: true,
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

        <template #right>
          <VideoImportModal />
        </template>
      </UDashboardNavbar>

      <UDashboardToolbar class="min-h-16">
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

        <template #right>
          <UFormField :error="form.errors.order">
            <USelect
              v-model="form.order"
              :items="orders"
              label-key="label"
              value-key="value"
              class="min-w-36"
              @update:modelValue="onSubmit"
            />
          </UFormField>
        </template>
      </UDashboardToolbar>
    </template>

    <template #body>
      <InfiniteScroll
        data="items"
        :buffer="200"
      >
        <UPageList divide>
          <UPageCard
            v-for="item in items?.data"
            :key="item.id"
            :to="edit.url(item.id)"
            variant="naked"
            class="py-4 first:pt-0 last:pb-0"
          >
            <div class="flex items-center justify-between">
              <UUser
                :name="item.title"
                :description="`${item.timestamp} • ${item.filesize}`"
                :avatar="{
                  alt: item.name,
                  src: item.thumb,
                  loading: 'lazy',
                  decoding: 'async',
                  class: 'rounded-sm size-14 me-1',
                }"
              />

              <div class="z-10 flex items-center gap-2">
                <UButton
                  icon="i-lucide-eye"
                  color="secondary"
                  variant="ghost"
                  size="sm"
                  :to="VideoController.url(item.id)"
                />

                <VideoDeleteModal :item="item" />
              </div>
            </div>
          </UPageCard>
        </UPageList>
      </InfiniteScroll>
    </template>
  </UDashboardPanel>
</template>
