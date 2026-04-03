<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

const props = defineProps<{
  results?: boolean
  orders?: SelectMenuItem[]
  order?: string | undefined
}>()

const form = useForm('get', '', {
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
  <USelectMenu
    v-if="orders?.length && results"
    v-model="form.order"
    :model-modifiers="{ nullable: true }"
    :items="orders"
    :search-input="false"
    :ui="{ base: 'px-0', content: 'min-w-40' }"
    placeholder="Sort by"
    label-key="label"
    value-key="value"
    variant="none"
    clear
    @update:modelValue="onSubmit"
    @clear="onSubmit"
  />
</template>
