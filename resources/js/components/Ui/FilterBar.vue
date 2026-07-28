<script setup lang="ts">
import { useQuery } from '@/composables/query'
import type { QueryFilter, QueryFilters } from '@/types'
import type { SelectMenuItem } from '@nuxt/ui'

const props = defineProps<{
  filters?: SelectMenuItem[]
  sorters?: SelectMenuItem[]
  filter?: QueryFilters
  sort?: QueryFilter
  results?: boolean
}>()

const { form, formFilters, onSubmit } = useQuery({
  filters: () => props.filters,
  filter: () => props.filter,
  sort: () => props.sort,
})
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
    @update:modelValue="onSubmit"
  />

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
