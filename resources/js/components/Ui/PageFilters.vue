<script setup lang="ts">
import type { RadioGroupItem } from '@nuxt/ui'
import { useForm } from 'laravel-precognition-vue-inertia'

interface Props {
  title?: string | undefined
  description?: string | undefined
  headline?: string | undefined
  items?: RadioGroupItem[] | undefined
  active?: string | undefined
}

const props = defineProps<Props>()

const form = useForm('get', '', {
  type: props.active || null,
})

const onSubmit = async () =>
  form.submit({
    preserveState: true,
    replace: true,
    reset: ['items'],
  })
</script>

<template>
  <UPageHeader
    :title
    :description
    :headline
    :ui="{
      root: 'py-4',
      wrapper: 'gap-3',
      headline: 'mb-0',
      title: 'line-clamp-2 font-serif text-lg font-semibold tracking-tight sm:text-xl',
    }"
  >
    <template #links>
      <URadioGroup
        v-model="form.type"
        orientation="horizontal"
        variant="card"
        indicator="hidden"
        size="xs"
        @update:modelValue="onSubmit"
        :items="items"
        :ui="{
          root: 'flex size-full items-center overflow-x-auto',
          fieldset: 'gap-1.5',
          item: 'rounded-md py-1.5',
        }"
      />
    </template>
  </UPageHeader>
</template>
