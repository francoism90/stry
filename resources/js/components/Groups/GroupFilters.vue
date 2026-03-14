<script setup lang="ts">
import type { SelectMenuItem } from '@nuxt/ui'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  orders?: SelectMenuItem[]
  order?: string | undefined
}>()

const form = useForm('get', '', {
  order: props.order,
  page: 1,
})

const onSubmit = () => {
  form.submit({
    only: ['items', 'type', 'order'],
    reset: ['items'],
    preserveState: true,
  })
}
</script>

<template>
  <UDashboardToolbar>
    <template #left>
      <USelectMenu
        v-if="orders?.length"
        v-model="form.order"
        :model-modifiers="{ nullable: true }"
        :items="orders"
        :ui="{ base: 'px-0', content: 'min-w-48' }"
        placeholder="Sort by"
        label-key="label"
        value-key="value"
        variant="none"
        clear
        @update:modelValue="onSubmit"
        @clear="onSubmit"
      />
    </template>
  </UDashboardToolbar>
</template>
