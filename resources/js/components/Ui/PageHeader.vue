<script setup lang="ts">
import type { SelectItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

interface Props {
  title?: string | undefined
  headline?: string | undefined
  search?: string | undefined
  filter?: string | undefined
  filters?: SelectItem[] | undefined
}

const props = defineProps<Props>()

const form = useForm('get', '', {
  filter: props.filter || '',
  search: props.search || '',
})

const onReset = () => {
  form.defaults({
    filter: undefined,
    search: undefined,
  })

  form.resetAndClearErrors()
}

const onSubmit = () => {
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'filter', 'search'],
    reset: ['items'],
  })
}

watchDebounced(
  () => form.search,
  () => onSubmit(),
  { debounce: 300, maxWait: 1000 },
)
</script>

<template>
  <UPageHeader
    :title="title"
    :headline="headline"
  />
</template>
