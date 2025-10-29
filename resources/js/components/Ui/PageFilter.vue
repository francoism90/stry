<script setup lang="ts">
import type { SelectItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'
import { computed } from 'vue'

interface Props {
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

const hasFilters = computed(() => props.filter?.length || props.search?.length)

watchDebounced(
  () => form.search,
  () => onSubmit(),
  { debounce: 300, maxWait: 1000 },
)
</script>

<template>
  <UForm
    class="flex items-center gap-2"
    :state="form"
    @submit="onSubmit"
  >
    <UButton
      v-if="hasFilters"
      variant="outline"
      title="Reset filters"
      icon="i-lucide-delete"
      size="sm"
      class="px-2"
      @click="onReset"
    />

    <UInput
      v-model="form.search"
      class="w-52 sm:w-64"
      placeholder="Search..."
    />

    <USelect
      v-if="filters?.length"
      v-model="form.filter"
      value-key="value"
      :items="filters"
      placeholder="Filter by"
      class="w-32 sm:w-36"
      @update:modelValue="onSubmit"
    />
  </UForm>
</template>
