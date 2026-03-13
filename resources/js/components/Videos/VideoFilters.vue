<script setup lang="ts">
import type { SelectMenuItem } from '@nuxt/ui'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  orders?: SelectMenuItem[]
  order?: string | undefined
}>()

const form = useForm('get', '/', {
  order: props.order,
  page: 1,
})

const onSubmit = () => {
  form.submit({
    only: ['items', 'order'],
    reset: ['items'],
    preserveState: true,
  })
}
</script>

<template>
  <UDashboardToolbar>
    <template #left>
      <USelect
        v-if="orders?.length"
        v-model="form.order"
        :items="orders"
        :ui="{ base: 'px-0', content: 'min-w-36' }"
        label-key="label"
        value-key="value"
        variant="none"
        @update:modelValue="onSubmit"
      />
    </template>
  </UDashboardToolbar>
</template>
