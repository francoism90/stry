<script setup lang="ts">
import type { SelectMenuItem } from '@nuxt/ui';
import { useForm } from 'laravel-precognition-vue-inertia';

const props = defineProps<{
  types?: SelectMenuItem[]
  type?: string | undefined
  orders?: SelectMenuItem[]
  order?: string | undefined
}>()

const form = useForm('get', '', {
  type: props.type,
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
  <USelectMenu
    v-if="types?.length"
    v-model="form.type"
    :model-modifiers="{ nullable: true }"
    :items="types"
    :search-input="false"
    :ui="{ base: 'px-0', content: 'min-w-40' }"
    placeholder="All types"
    label-key="label"
    value-key="value"
    variant="none"
    clear
    @update:modelValue="onSubmit"
    @clear="onSubmit"
  />

  <USelectMenu
    v-if="orders?.length"
    v-model="form.order"
    :model-modifiers="{ nullable: true }"
    :items="orders"
    :search-input="false"
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
