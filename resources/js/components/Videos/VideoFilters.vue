<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

const props = defineProps<{
  results?: boolean
  sorters?: SelectMenuItem[]
  sort?: string | undefined
  captioned?: string | undefined
}>()

const captionedOptions: SelectMenuItem[] = [{ label: 'Captioned', value: 'true' }]

const form = useForm('get', '', {
  sort: props.sort,
  'filter[captioned]': props.captioned,
  'page[number]': 1,
})

const onSubmit = () => {
  form.submit({
    only: ['items', 'sort', 'filters'],
    reset: ['items'],
    preserveState: true,
  })
}
</script>

<template>
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

  <USelectMenu
    v-model="form['filter[captioned]']"
    :model-modifiers="{ nullable: true }"
    :items="captionedOptions"
    :search-input="false"
    :ui="{ base: 'px-0', content: 'min-w-36' }"
    placeholder="All videos"
    label-key="label"
    value-key="value"
    variant="none"
    clear
    @update:modelValue="onSubmit"
    @clear="onSubmit"
  />
</template>
