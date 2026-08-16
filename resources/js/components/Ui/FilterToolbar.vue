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
  filter: () => props.filter ?? { scope: 'all' },
  sort: () => props.sort ?? 'recommended',
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
        v-model="form.filter.scope"
        :items="scopes"
        default-value="all"
        variant="card"
        size="sm"
        orientation="horizontal"
        color="neutral"
        indicator="end"
        @update:model-value="onSubmit"
        :ui="{
          container: 'sr-only',
          wrapper: 'me-0',
          item: 'border-0 bg-neutral-800/75 px-2.5 py-1.5 has-data-[state=checked]:bg-white has-data-[state=checked]:text-black',
          label: 'text-inherit',
        }"
      />
    </template>

    <template #right>
      <USelect
        v-if="sorters"
        v-model="form.sort"
        :items="sorters"
        placeholder="Sort by"
        class="w-36"
        @update:model-value="onSubmit"
      />
    </template>
  </UDashboardToolbar>
</template>
