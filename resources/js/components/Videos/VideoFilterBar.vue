<script setup lang="ts">
import type { VideoFilters } from '@/types'
import { modelFilters } from '@/utils/model'
import { useForm } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import { computed } from 'vue'

const props = defineProps<{
  results?: boolean
  sorters?: SelectMenuItem[]
  sort?: string | undefined
  filters?: VideoFilters
}>()

const videoOptions: SelectMenuItem[] = [
  { label: 'Captioned', value: 'captioned' },
  { label: 'Unseen', value: 'unseen' },
]

const activeVideoFilters = computed(() => modelFilters(props.filters))

const form = useForm('get', '', {
  sort: props.sort,
  'filter[captioned]': props.filters?.captioned,
  'filter[unseen]': props.filters?.unseen,
  'page[number]': 1,
})

const onFiltersChange = (values: string[]) => {
  form['filter[captioned]'] = values.includes('captioned') ? 'true' : undefined
  form['filter[unseen]'] = values.includes('unseen') ? 'true' : undefined
  onSubmit()
}

const onSubmit = () => {
  form.submit({
    only: ['items', 'filters', 'sort'],
    reset: ['items'],
    preserveState: true,
  })
}
</script>

<template>
  <USelectMenu
    :model-value="activeVideoFilters"
    :items="videoOptions"
    :search-input="false"
    :ui="{ base: 'px-0', content: 'min-w-40' }"
    placeholder="All videos"
    label-key="label"
    value-key="value"
    variant="none"
    multiple
    clear
    @update:model-value="onFiltersChange"
    @clear="() => onFiltersChange([])"
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
    @clear="onSubmit"
  />
</template>
