<script setup lang="ts">
import { useQuery } from '@/composables/query'
import type { QueryFilter, QueryFilters } from '@/types'
import { usePage } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'

defineProps<{
  placeholder?: string
}>()

const page = usePage<{
  filters?: SelectMenuItem[]
  sorters?: SelectMenuItem[]
  filter?: QueryFilters
  query?: QueryFilter
  sort?: QueryFilter
}>()

const { form, formFilters, onSubmit } = useQuery({
  filters: () => page.props.filters,
  filter: () => page.props.filter,
  sort: () => page.props.sort,
  query: () => page.props.query,
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
        autofocus
      />
    </UFormField>

    <USelectMenu
      v-if="page.props.filters?.length"
      v-model="formFilters"
      :model-modifiers="{ nullable: true }"
      :items="page.props.filters"
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
      v-if="page.props.sorters?.length"
      v-model="form.sort"
      :model-modifiers="{ nullable: true }"
      :items="page.props.sorters"
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
