<script setup lang="ts">
import type { SelectMenuItem } from '@nuxt/ui'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  types?: SelectMenuItem[]
  type?: string | undefined
}>()

const form = useForm('get', '', {
  type: props.type,
  page: 1,
})

const onSubmit = () => {
  form.submit({
    only: ['items', 'order'],
    reset: ['items'],
    preserveState: true,
  })
}
</script>

<template>
  <UDashboardToolbar>
    <template #left>
      <USelectMenu
        v-if="types?.length"
        v-model="form.type"
        :model-modifiers="{ nullable: true }"
        :items="types"
        :ui="{ base: 'px-0', content: 'min-w-40' }"
        placeholder="All types"
        label-key="label"
        value-key="value"
        variant="none"
        clear
        @update:modelValue="onSubmit"
        @clear="onSubmit"
      />
    </template>
  </UDashboardToolbar>
</template>
