<script setup lang="ts">
import type { RadioGroupItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

interface Props {
  items: RadioGroupItem[] | undefined
  active: string | undefined
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

watchDebounced(
  () => form.type,
  () => onSubmit(),
  { debounce: 300, maxWait: 1000 },
)
</script>

<template>
  <div>
    <URadioGroup
      v-model="form.type"
      orientation="horizontal"
      variant="card"
      :items="items"
      :ui="{
        root: 'size-full items-center overflow-x-auto',
      }"
    />
    {{ form.type || 'No filter selected' }}
  </div>
</template>
