<script setup lang="ts">
import FormModal from '@/components/Ui/FormModal.vue'
import { passkeyErrorMessage } from '@/composables/passkeys'
import { Passkeys } from '@laravel/passkeys'
import { ref } from 'vue'

const open = defineModel<boolean>('open')

const emit = defineEmits<{
  created: []
}>()

const name = ref('')
const error = ref<string | null>(null)
const processing = ref(false)

const onSubmit = async (close: () => void) => {
  if (!name.value.trim()) {
    error.value = 'Give this passkey a name.'

    return
  }

  processing.value = true
  error.value = null

  try {
    await Passkeys.register({ name: name.value.trim() })

    name.value = ''
    close()
    emit('created')
  } catch (exception) {
    error.value = passkeyErrorMessage(exception)
  } finally {
    processing.value = false
  }
}
</script>

<template>
  <FormModal
    v-model:open="open"
    title="Add passkey"
    description="Your browser or password manager will ask you to create a passkey."
    submit-label="Continue"
    :processing="processing"
    @submit="onSubmit"
  >
    <template #body>
      <UFormField
        label="Name"
        description="So you can recognize it later, e.g. MacBook or Phone."
        required
        :error="error ?? undefined"
      >
        <UInput
          v-model="name"
          :model-modifiers="{ trim: true }"
          autofocus
          maxlength="255"
          class="w-full"
        />
      </UFormField>
    </template>
  </FormModal>
</template>
