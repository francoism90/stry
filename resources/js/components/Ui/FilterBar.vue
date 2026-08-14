<script setup lang="ts">
import { useQuery } from '@/composables/query'
import type { QueryFilter, QueryValue } from '@/types'
import type { SelectMenuItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'

const props = defineProps<{
  filters?: SelectMenuItem[]
  sorters?: SelectMenuItem[]
  filter?: QueryFilter
  query?: QueryValue
  sort?: QueryValue
  placeholder?: string
}>()

const { form, formFilters, onSubmit } = useQuery({
  filters: () => props.filters,
  filter: () => props.filter,
  sort: () => props.sort,
  query: () => props.query,
})

watchDebounced(
  () => form.query,
  () => onSubmit(),
  { debounce: 350, maxWait: 1000 },
)
</script>

<template>
  <UDashboardToolbar>
    <UFormField
      :error="form.errors.query"
      class="min-w-3/4 flex-1"
    >
      <UInput
        v-model="form.query"
        :model-modifiers="{ nullable: true, string: true, trim: true }"
        :placeholder="placeholder ?? 'Search...'"
        variant="soft"
        size="lg"
        color="neutral"
        icon="i-lucide-search"
      />
    </UFormField>

    <USelectMenu
      v-if="filters?.length"
      v-model="formFilters"
      :model-modifiers="{ nullable: true }"
      :items="filters"
      :search-input="false"
      :ui="{ content: 'min-w-40' }"
      placeholder="Filters"
      label-key="label"
      value-key="value"
      variant="ghost"
      size="lg"
      multiple
      clear
      @update:modelValue="onSubmit"
    />

    <USelectMenu
      v-if="sorters?.length"
      v-model="form.sort"
      :model-modifiers="{ nullable: true }"
      :items="sorters"
      :search-input="false"
      :ui="{ content: 'min-w-40' }"
      placeholder="Sort by"
      label-key="label"
      value-key="value"
      variant="ghost"
      size="lg"
      clear
      @update:modelValue="onSubmit"
    />
  </UDashboardToolbar>
</template>
