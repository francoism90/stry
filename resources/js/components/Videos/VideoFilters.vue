<script setup lang="ts">
import type { Tag } from '@/types'
import type { SelectMenuItem } from '@nuxt/ui'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  orders: SelectMenuItem[]
  tag?: Tag | undefined
  order?: string | undefined
}>()

const form = useForm('get', '/', {
  order: props.order,
  tag: props.tag?.id,
  page: 1,
})

const onSubmit = () => {
  form.submit({
    preserveState: true,
    only: ['items', 'order', 'tag'],
    reset: ['items'],
  })
}

const clearTag = () => {
  form.tag = undefined
  onSubmit()
}
</script>

<template>
  <UDashboardToolbar>
    <template #left>
      <USelect
        v-show="!!orders.length"
        v-model="form.order"
        :items="orders"
        :ui="{ content: 'min-w-36' }"
        label-key="label"
        value-key="value"
        variant="soft"
        size="sm"
        @update:modelValue="onSubmit"
      />

      <UButton
        v-if="tag"
        :label="tag.name"
        color="primary"
        size="xs"
        trailing-icon="i-lucide-x"
        @click.prevent="clearTag"
      />
    </template>
  </UDashboardToolbar>
</template>
