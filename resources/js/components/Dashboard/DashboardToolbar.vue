<script setup lang="ts">
import { useCollection } from '@/composables/collection'
import type { RadioGroupItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'
import { ref } from 'vue'

const { filter, search, filters, view } = useCollection()

const props = defineProps<{
  defaultFilter?: string
  defaultSearch?: string
  defaultView?: string
}>()

const form = useForm('get', '', {
  filter: filter.value || props.defaultFilter,
  search: search.value || props.defaultSearch,
  view: view.value || props.defaultView,
})

const views = ref<RadioGroupItem[]>([
  {
    label: 'List View',
    value: 'vertical',
  },
  {
    label: 'Grid View',
    value: 'horizontal',
  },
])

const onSubmit = () => {
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'filter', 'search', 'view'],
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
  <UForm
    :state="form"
    @submit="onSubmit"
  >
    <UDashboardToolbar>
      <template #left>
        <USelect
          v-if="filters?.length"
          v-model="form.filter"
          :items="filters"
          :default-value="defaultFilter || 'recommended'"
          value-key="value"
          placeholder="Filter by"
          variant="soft"
          class="w-32 sm:w-36"
          @update:modelValue="onSubmit"
        />
      </template>

      <template #right>
        <URadioGroup
          v-model="form.view"
          :items="views"
          :default-value="defaultView || 'vertical'"
          value-key="value"
          orientation="horizontal"
          variant="list"
          size="xs"
          class="max-lg:hidden"
          @update:modelValue="onSubmit"
        />

        <UInput
          v-model="form.search"
          class="w-52 sm:w-64"
          placeholder="Search..."
        />
      </template>
    </UDashboardToolbar>
  </UForm>
</template>
