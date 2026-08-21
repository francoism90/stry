<script setup lang="ts">
import type { ModalProps, TabsItem } from '@nuxt/ui'

const open = defineModel<boolean>('open')

withDefaults(
  defineProps<{
    title: string
    description?: string
    submitLabel?: string
    processing?: boolean
    tabs?: TabsItem[]
    ui?: ModalProps['ui']
  }>(),
  {
    submitLabel: 'Save changes',
  },
)

const emit = defineEmits<{
  submit: [close: () => void]
}>()
</script>

<template>
  <UModal
    v-model:open="open"
    :title="title"
    :description="description"
    :ui="{ footer: 'justify-end', ...ui }"
  >
    <template v-if="$slots.default" #default>
      <slot />
    </template>

    <template #body>
      <UTabs
        v-if="tabs?.length"
        :items="tabs"
        :ui="{ trigger: 'grow' }"
      >
        <template
          v-for="tab in tabs"
          :key="tab.value"
          #[tab.slot]
        >
          <slot :name="tab.slot" />
        </template>
      </UTabs>

      <slot v-else name="body" />
    </template>

    <template #footer="{ close }">
      <UButton
        label="Cancel"
        color="neutral"
        variant="soft"
        @click.prevent="close"
      />

      <UButton
        :label="submitLabel"
        color="primary"
        variant="soft"
        :loading="processing"
        @click.prevent="emit('submit', close)"
      />
    </template>
  </UModal>
</template>
