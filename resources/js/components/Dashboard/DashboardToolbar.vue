<script setup lang="ts">
import { useCollection } from '@/composables/collection'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

const { order, search, orders } = useCollection()

const form = useForm('get', '', {
  order: order.value,
  search: search.value,
})

const onSubmit = () => {
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'order', 'search'],
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
          v-if="orders?.length"
          v-model="form.order"
          value-key="value"
          :items="orders"
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
