<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

const props = defineProps<{
  results?: boolean
  type?: string | null
  sort?: string | null
  types?: SelectMenuItem[]
  sorters?: SelectMenuItem[]
}>()

const form = useForm('get', '', {
  sort: props.sort,
  'filter[type]': props.type,
  'page[number]': 1,
})

const onSubmit = () => {
  router.reload({
    data: form.data(),
    only: ['items', 'filter', 'sort'],
    reset: ['items'],
  })
}
</script>

<template>
  <USelectMenu
    v-if="types?.length"
    v-model="form['filter[type]']"
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
  />
</template>
