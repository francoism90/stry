<script setup lang="ts">
import type { SelectItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

interface Props {
  title?: string | undefined
  description?: string | undefined
  headline?: string | undefined
  search?: string | undefined
  sort?: string | undefined
  types?: SelectItem[] | undefined
  type?: string | undefined
}

const props = defineProps<Props>()

const form = useForm('get', '', {
  search: props.search || null,
  sort: props.sort || null,
  type: null,
})

const onReset = () => {
  form.defaults({
    search: '',
    sort: null,
    type: null,
  })

  form.resetAndClearErrors()
}

const onSubmit = () => {
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'search', 'sort', 'type'],
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
        <UInput
          v-model="form.search"
          class="w-60"
          placeholder="Search..."
        />

        <USelect
          v-if="types?.length"
          v-model="form.type"
          placeholder="Filter by type"
          class="w-36"
          value-key="value"
          :items="types"
          @update:modelValue="onSubmit"
        />

        <UButton
          v-if="form.type || form.search || form.sort"
          variant="outline"
          icon="i-lucide-delete"
          size="sm"
          class="px-2"
          @click="onReset"
        />
      </UForm>
    </template>
  </UPageHeader>
</template>
