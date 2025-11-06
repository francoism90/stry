<script setup lang="ts">
import { useCollection } from '@/composables/collection'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

const { filter, search, filters, view } = useCollection()

const form = useForm('get', '', {
  filter: filter.value,
  search: search.value,
  view: view.value,
})

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
          value-key="value"
          :items="filters"
          placeholder="Filter by"
          variant="soft"
          class="w-32 sm:w-36"
          @update:modelValue="onSubmit"
        />
      </template>

      <template #right>
        <UInput
          v-model="form.search"
          class="w-52 sm:w-64"
          placeholder="Search..."
        />
      </template>
    </UDashboardToolbar>
  </UForm>
</template>
