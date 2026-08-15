<script setup lang="ts">
import { useQuery } from '@/composables/query'
import type { QueryFilter, QueryValue } from '@/types'
import type { SelectItem, SelectMenuItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'

const props = defineProps<{
  scopes?: SelectMenuItem[]
  sorters?: SelectItem[]
  filter?: QueryFilter
  sort?: QueryValue
  query?: QueryValue
}>()

const { form, onSubmit } = useQuery({
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
    <template #left>
      <URadioGroup
        v-if="scopes"
        variant="card"
        size="sm"
        orientation="horizontal"
        color="neutral"
        indicator="end"
        default-value="all"
        v-model="form.filter.scope"
        :items="scopes"
        :ui="{
          container: 'sr-only',
          wrapper: 'me-0',
          item: 'border-0 bg-neutral-800/75 px-2 py-1.5 has-data-[state=checked]:bg-white has-data-[state=checked]:text-black',
          label: 'text-inherit',
        }"
        @update:model-value="onSubmit"
      />
    </template>

    <template #right>
      <USelect
        v-if="sorters"
        size="sm"
        placeholder="Sort by"
        class="w-36"
        default-value="recommended"
        v-model="form.sort"
        :items="sorters"
        @update:model-value="onSubmit"
      />
    </template>
  </UDashboardToolbar>
</template>
