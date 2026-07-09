<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

const props = defineProps<{
  sorters?: SelectMenuItem[]
  results?: boolean | null
  sort?: string | null
}>()

const form = useForm('get', '', {
  sort: props.sort,
  'page[number]': 1,
})

const onSubmit = () => {
  router.reload({
    data: form.data(),
    only: ['items', 'sort'],
    reset: ['items'],
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
    :ui="{ base: 'px-0', content: 'min-w-48' }"
    placeholder="Sort by"
    label-key="label"
    value-key="value"
    variant="none"
    clear
    @update:modelValue="onSubmit"
  />
</template>
