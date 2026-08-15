<script setup lang="ts">
import { useQuery } from '@/composables/query'
import type { QueryValue } from '@/types'
import type { RadioGroupItem } from '@nuxt/ui'

const props = defineProps<{
  items?: RadioGroupItem[]
  active?: QueryValue
}>()

const { form, onSubmit } = useQuery({
  filters: () => props.items,
  filter: () => props.active,
})
</script>

<template>
  <URadioGroup
    variant="card"
    size="sm"
    default-value="all"
    label-key="label"
    value-key="value"
    orientation="horizontal"
    color="neutral"
    indicator="end"
    :items="items"
    :ui="{
      container: 'sr-only',
      wrapper: 'me-0',
      item: 'border-0 bg-neutral-800/75 px-2 py-1.5 has-data-[state=checked]:bg-white has-data-[state=checked]:text-black',
      label: 'text-inherit',
    }"
  />
</template>
