<script setup lang="ts">
import { QueryInjectionKey } from '@/composables/query'
import type { OptionItem } from '@/types'
import { defineProps, inject } from 'vue'

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
          item: 'shrink-0 rounded-lg border border-(--glass-border) bg-(--glass) px-3 py-1.5 font-medium text-highlighted backdrop-blur-md backdrop-saturate-140 has-data-[state=checked]:border-white has-data-[state=checked]:bg-white has-data-[state=checked]:text-black',
          label: 'flex items-center gap-1.5 text-inherit',
        }"
      >
        <template #label="{ item }">
          <UIcon
            v-if="typeof item === 'object' && item.icon"
            :name="item.icon"
            class="size-4 shrink-0"
          />
          {{ typeof item === 'object' ? item.label : item }}
        </template>
      </URadioGroup>
    </template>

    <template #right>
      <USelect
        v-if="sorters"
        v-model="form.sort"
        :items="sorters"
        placeholder="Sort by"
        class="w-38 text-sm"
        @update:model-value="onSubmit"
        :ui="{
          base: 'rounded-lg bg-(--glass) font-medium text-highlighted ring-(--glass-border) backdrop-blur-md backdrop-saturate-140',
        }"
      />
    </template>
  </UDashboardToolbar>
</template>
