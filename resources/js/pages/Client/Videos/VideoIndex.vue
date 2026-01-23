<script setup lang="ts">
import UserMenu from '@/components/Ui/UserMenu.vue'
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
      <UDashboardNavbar
        :ui="{ root: 'h-20 gap-3 border-0', left: 'w-full' }"
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
          <UserMenu />
        </template>
      </UDashboardNavbar>

      <UDashboardToolbar
        id="video-header"
        class="min-h-8 border-0"
      >
        <template #left>
          <UFormField
            v-show="items.data?.length"
            orientation="horizontal"
            label="Sort by"
            :ui="{ label: 'text-secondary-400 text-xs' }"
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
    </template>

    <template #body>
      <UPage>
        <InfiniteScroll
          data="items"
          start-element="#video-header"
          items-element="#video-list"
          :buffer="200"
        >
          <VideoList
            id="video-list"
            :items="items?.data"
          />
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
