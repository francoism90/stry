<script setup lang="ts">
import type { QueryFilter, QueryFilters } from '@/types'
import { router, useForm } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import { computed } from 'vue'

const props = defineProps<{
  filters?: SelectMenuItem[]
  sorters?: SelectMenuItem[]
  filter?: QueryFilters
  sort?: QueryFilter
  results?: number
}>()

const form = useForm('get', '', {
  sort: props.sort,
  filters: props.filter,
  'page[number]': 1,
})

const formFilters = computed<string[]>({
  get: () => (['captioned', 'shorts', 'unseen', 'untagged'] as const).filter((key) => !!form[`filter[${key}]`]),
  set: (values) => {
    form['filter[captioned]'] = values.includes('captioned') ? 'true' : undefined
    form['filter[shorts]'] = values.includes('shorts') ? 'true' : undefined
    form['filter[unseen]'] = values.includes('unseen') ? 'true' : undefined
    form['filter[untagged]'] = values.includes('untagged') ? 'true' : undefined
    onSubmit()
  },
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
    v-if="filters?.length"
    v-model="formFilters"
    :model-modifiers="{ nullable: true }"
    :items="filters"
    :search-input="false"
    :ui="{ base: 'px-0', content: 'min-w-40' }"
    placeholder="Filters"
    label-key="label"
    value-key="value"
    variant="none"
    multiple
    clear
  />

  {{ filter }}

  <USelectMenu
    v-if="sorters?.length && results"
    v-model="form.sort"
    :model-modifiers="{ nullable: true }"
    :items="sorters"
    :search-input="false"
    :ui="{ base: 'px-0', content: 'min-w-40' }"
    placeholder="Sort by"
    label-key="label"
    value-key="value"
    variant="none"
    clear
    @update:modelValue="onSubmit"
  />
</template>
