<script setup lang="ts">
import VideoList from '@/components/Videos/VideoList.vue'
import type { VideoCollection } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: VideoCollection
  search: string | null
  sort: string | null
  filter: string | number | undefined
  filters: SelectMenuItem[]
  sorters: SelectMenuItem[]
}>()

const form = useForm('get', '', {
  search: props.search,
  filter: props.filter,
  sort: props.sort,
  page: 1,
})

const onSubmit = () => {
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'search', 'sort', 'filter'],
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
  <UDashboardPanel id="feed">
    <template #header>
      <UForm
        id="library"
        :state="form"
        loading-auto
        @submit="onSubmit"
      >
        <UDashboardNavbar :ui="{ root: 'h-24 gap-3 border-0', left: 'w-full' }">
          <template #left>
            <UFormField
              :error="form.errors.search"
              class="flex-1"
            >
              <UInput
                v-model="form.search"
                :model-modifiers="{ nullable: true, string: true, trim: true }"
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
              size="xl"
              color="neutral"
              icon="i-lucide-settings"
              :ui="{ base: 'p-3', leadingIcon: 'size-4' }"
            />
          </template>
        </UDashboardNavbar>

        <UDashboardToolbar>
          <UFormField
            :error="form.errors.sort"
            class="flex-none"
          >
            <USelect
              v-model="form.sort"
              :items="sorters"
              label-key="label"
              value-key="value"
              placeholder="Filter by"
              variant="soft"
              size="lg"
              @update:modelValue="onSubmit"
            />
          </UFormField>
        </UDashboardToolbar>
      </UForm>
    </template>

    <template #body>
      <UPage>
        <InfiniteScroll
          data="items"
          :buffer="200"
        >
          <VideoList :items="items" />
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
