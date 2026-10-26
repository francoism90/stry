<script setup lang="ts">
import type { Passkey } from '@/types'
import { ref } from 'vue'

const props = defineProps<{
  item?: Passkey
  remove: (passkey: Passkey) => Promise<void>
}>()

const open = defineModel<boolean>('open')

const processing = ref(false)

const handle = async () => {
  if (!props.item) {
    return
  }

  processing.value = true

  try {
    await props.remove(props.item)

    open.value = false
  } finally {
    processing.value = false
  }
}
</script>

<template>
  <UModal
    v-model:open="open"
    :title="item?.name ?? 'Passkey'"
    :ui="{ footer: 'justify-end' }"
  >
    <template #body>
      <div class="flex flex-col gap-2">
        <h3>Are you sure you want to delete this passkey?</h3>
        <p class="text-sm text-neutral-500">
          You won't be able to sign in with it anymore. Remove it from your device or password manager as well.
        </p>
      </div>
    </template>

    <template #footer="{ close }">
      <UButton
        label="Cancel"
        color="neutral"
        variant="soft"
        @click.prevent="close"
      />

      <UButton
        label="Delete passkey"
        color="error"
        variant="soft"
        :loading="processing"
        @click.prevent="handle"
      />
    </template>
  </UModal>
</template>
