<script setup lang="ts">
import type { SelectItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

interface Props {
  title?: string | undefined
  description?: string | undefined
  headline?: string | undefined
  search?: string | undefined
  filter?: string | undefined
  filters?: SelectItem[] | undefined
}

const props = defineProps<Props>()

const form = useForm('get', '', {
  filter: props.filter,
  search: props.search,
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
    :title
    :description
    :headline
    :ui="{
      root: 'pt-0 pb-4',
      wrapper: 'gap-2',
      headline: 'mb-0',
      title: 'line-clamp-2 font-serif text-lg font-semibold tracking-tight sm:text-xl',
    }"
  >
    <template #links>
      <UForm
        class="flex items-center gap-2"
        :state="form"
        @submit="onSubmit"
      >
        <UButton
          v-if="form.filter || form.search"
          variant="outline"
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
          placeholder="Filter"
          class="w-32 sm:w-36"
          @update:modelValue="onSubmit"
        />
      </UForm>
    </template>
  </UPageHeader>
</template>
