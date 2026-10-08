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

const onEnter = (event: KeyboardEvent, close: () => void): void => {
  if (event.defaultPrevented || !(event.target instanceof HTMLInputElement)) {
    return
  }

  event.preventDefault()
  emit('submit', close)
}
</script>

<template>
  <UModal
    v-model:open="open"
    :title="title"
    :description="description"
    :ui="{ footer: 'justify-end', ...ui }"
  >
    <template
      v-if="$slots.default"
      #default
    >
      <slot />
    </template>

    <template #body="{ close }">
      <div
        @submit.prevent="emit('submit', close)"
        @keydown.enter="onEnter($event, close)"
      >
        <UTabs
          v-if="tabs?.length"
          :items="tabs"
          variant="pill"
          color="neutral"
          size="sm"
          class="w-full gap-4"
          :ui="{
            list: 'rounded-full bg-(--glass)',
            indicator: 'rounded-full bg-(--glass-strong) shadow-none',
            trigger: 'grow rounded-full data-[state=active]:text-highlighted',
          }"
        >
          <template
            v-for="tab in tabs"
            :key="tab.value"
            #[tab.slot]
          >
            <slot :name="tab.slot" />
          </template>
        </UTabs>

        <slot
          v-else
          name="body"
        />
      </div>
    </template>

    <template #footer="{ close }">
      <UButton
        label="Cancel"
        color="neutral"
        variant="soft"
        class="rounded-full bg-(--glass) hover:bg-(--glass-strong)"
        @click.prevent="close"
      />

      <UButton
        :label="submitLabel"
        color="primary"
        variant="solid"
        class="rounded-full"
        :loading="processing"
        @click.prevent="emit('submit', close)"
      />
    </template>
  </UModal>
</template>
