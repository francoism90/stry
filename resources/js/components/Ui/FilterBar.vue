<script setup lang="ts">
import { useQuery } from '@/composables/query'
import type { QueryFilter, QueryFilters } from '@/types'
import type { SelectMenuItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'

const props = defineProps<{
  results?: boolean
  placeholder?: string
  filters?: SelectMenuItem[]
  sorters?: SelectMenuItem[]
  filter?: QueryFilters
  query?: QueryFilter
  sort?: QueryFilter
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
    <div class="mx-auto flex w-full max-w-(--ui-container) flex-col gap-2 py-3 md:flex-row">
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
        v-if="sorters?.length && results"
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
    </div>
  </UDashboardToolbar>
</template>
