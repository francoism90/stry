<script setup lang="ts">
import type { VideoFilters } from '@/types'
import { router, useForm } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import { computed } from 'vue'

const props = defineProps<{
  results?: boolean
  sorters?: SelectMenuItem[]
  scopes?: SelectMenuItem[]
  sort?: string | undefined
  filters?: VideoFilters
}>()

const form = useForm('get', '', {
  sort: props.sort,
  'filter[captioned]': props.filters?.captioned,
  'filter[unseen]': props.filters?.unseen,
  'page[number]': 1,
})

const formFilters = computed<string[]>({
  get: () => (['captioned', 'unseen'] as const).filter((key) => !!form[`filter[${key}]`]),
  set: (values) => {
    form['filter[captioned]'] = values.includes('captioned') ? 'true' : undefined
    form['filter[unseen]'] = values.includes('unseen') ? 'true' : undefined
    onSubmit()
  },
})

const onSubmit = () => {
  router.reload({
    data: form.data(),
    only: ['items', 'filter', 'sort'],
    reset: ['items'],
  })
}
</script>

<template>
  <USelectMenu
    v-if="scopes?.length"
    v-model="formFilters"
    :items="scopes"
    :search-input="false"
    :ui="{ base: 'px-0', content: 'min-w-40' }"
    placeholder="Filters"
    label-key="label"
    value-key="value"
    variant="none"
    multiple
    clear
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
