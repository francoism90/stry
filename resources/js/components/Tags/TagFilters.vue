<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

const props = defineProps<{
  results?: boolean
  type?: string | undefined
  sort?: string | undefined
  types?: SelectMenuItem[]
  sorters?: SelectMenuItem[]
}>()

const form = useForm('get', '', {
  type: props.type,
  sort: props.sort,
  'page[number]': 1,
})

const onSubmit = () => {
  form.submit({
    only: ['items', 'type', 'sort'],
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
    v-if="sorters?.length && results"
    v-model="form.sort"
    :model-modifiers="{ nullable: true }"
    :items="sorters"
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
