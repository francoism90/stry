<script setup lang="ts">
import { useCollection } from '@/composables/collection'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

interface Props {
  defaultFilter?: string | null
  defaultSearch?: string | null
  defaultGrid?: boolean | undefined
}

const props = defineProps<Props>()

const { filter, search, filters, grid } = useCollection()

const form = useForm('get', '', {
  filter: filter.value || props.defaultFilter,
  search: search.value || props.defaultSearch,
  grid: grid.value || props.defaultGrid,
})

const onSubmit = () => {
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'filter', 'search', 'grid'],
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
          value-key="value"
          :items="filters"
          placeholder="Filter by"
          variant="soft"
          class="w-32 sm:w-36"
          @update:modelValue="onSubmit"
        />
      </template>

      <template #right>
        <USwitch
          v-model="form.grid"
          label="Grid View"
          size="xs"
          class="hidden sm:flex"
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
