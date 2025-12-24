<script setup lang="ts">
import type { TagCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: TagCollection
  search: string | null
  type: string | number | undefined
  types: SelectMenuItem[]
}>()

const form = useForm('get', '', {
  search: props.search,
  type: props.type,
  page: 1,
})

const onSubmit = () => {
  form.submit({
    preserveState: true,
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

  <UDashboardPanel id="tags">
    <template #header>
      <UForm
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

        <UDashboardToolbar :ui="{ root: 'min-h-0 border-0', left: 'gap-2' }">
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
      </UForm>
    </template>

    <template #body>
      <UPage>
        <InfiniteScroll data="items">
          {{ items }}

          <!-- <TagList :items="items" /> -->
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
