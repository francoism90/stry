<script setup lang="ts">
import VideoList from '@/components/Videos/VideoList.vue'
import type { VideoCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: VideoCollection
  orders: SelectMenuItem[] | undefined
  filter: string | undefined
  search: string | undefined
  order: string | undefined
}>()

const form = useForm('get', '', {
  search: props.search,
  order: props.order,
  page: 1,
})

const onSubmit = () => {
  form.submit({
    preserveState: true,
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
  <Head :title="filter" />

  <UDashboardPanel id="library">
    <template #header>
      <UForm
        id="video-header"
        :state="form"
        loading-auto
        @submit="onSubmit"
      >
        <UDashboardNavbar
          :ui="{ root: 'h-24 gap-3 border-0', left: 'w-full' }"
          :toggle="{ variant: 'link', class: 'ps-0' }"
        >
          <template #left>
            <UFormField
              :error="form.errors.search"
              class="flex-1"
            >
              <UInput
                v-model="form.search"
                :model-modifiers="{ string: true, trim: true }"
                variant="soft"
                size="xl"
                color="neutral"
                placeholder="Search..."
                icon="i-lucide-search"
              />
            </UFormField>
          </template>

          <template #right>
            <UButton
              variant="soft"
              size="xs"
              color="neutral"
              icon="i-lucide-settings"
              class="p-3"
            />
          </template>
        </UDashboardNavbar>

        <UDashboardToolbar :ui="{ root: 'min-h-0 border-0', left: 'gap-2' }">
          <template #left>
            <UFormField
              v-show="items.data?.length"
              orientation="horizontal"
              label="Sort by"
              :ui="{ label: 'text-xs text-secondary-400' }"
              :error="form.errors.order"
            >
              <USelect
                v-model="form.order"
                :items="orders"
                :ui="{ content: 'min-w-36' }"
                label-key="label"
                value-key="value"
                variant="soft"
                size="sm"
                @update:modelValue="onSubmit"
              />
            </UFormField>
          </template>
        </UDashboardToolbar>
      </UForm>
    </template>

    <template #body>
      <UPage>
        <InfiniteScroll
          data="items"
          items-element="#video-list"
          start-element="#video-header"
          :buffer="200"
        >
          <VideoList
            id="video-list"
            :items="items"
          />
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
