<script setup lang="ts">
import TagList from '@/components/Tags/TagList.vue'
import type { TagCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: TagCollection
  types: SelectMenuItem[] | undefined
  search: string | undefined
  type: string | undefined
}>()

const form = useForm('get', '', {
  search: props.search,
  type: props.type,
  page: 1,
})

const onSubmit = () => {
  form.submit({
    preserveState: 'errors',
    replace: true,
    only: ['items', 'search', 'type'],
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
  <Head title="Tags" />

  <UDashboardPanel id="taggables">
    <template #header>
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
            size="xl"
            color="neutral"
            icon="i-lucide-settings"
            :ui="{ base: 'p-3', leadingIcon: 'size-4' }"
          />
        </template>
      </UDashboardNavbar>

      <UDashboardToolbar
        id="tag-header"
        class="min-h-8 border-0"
      >
        <template #left>
          <UFormField
            orientation="horizontal"
            label="Type"
            :ui="{ label: 'text-xs text-secondary-400' }"
            :error="form.errors.type"
          >
            <USelect
              v-model="form.type"
              :items="types"
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
          start-element="#tag-header"
          items-element="#tag-list"
        >
          <TagList
            id="tag-list"
            :items="items?.data"
          />
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
