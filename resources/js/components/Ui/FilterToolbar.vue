<script setup lang="ts">
import { QueryInjectionKey } from '@/composables/query'
import type { OptionItem } from '@/types'
import { inject } from 'vue'

defineProps<{
  scopes?: OptionItem[]
  sorters?: OptionItem[]
}>()

const { form, onSubmit } = inject(QueryInjectionKey)!
</script>

<template>
  <UDashboardToolbar>
    <template #left>
      <URadioGroup
        v-if="scopes"
        v-model="form.filter.scope"
        :items="scopes"
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
