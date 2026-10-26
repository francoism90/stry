<script setup lang="ts">
import FormModal from '@/components/Ui/FormModal.vue'
import { passkeyErrorMessage } from '@/composables/passkeys'
import { confirm, confirmOptions } from '@/routes/passkey'
import { store } from '@/routes/password/confirm'
import { useHttp } from '@inertiajs/vue3'
import { Passkeys } from '@laravel/passkeys'
import { ref } from 'vue'

defineProps<{
  hasPasskeys?: boolean
}>()

const open = defineModel<boolean>('open')

const emit = defineEmits<{
  confirmed: []
}>()

const http = useHttp({
  password: '',
})

const verifying = ref(false)

const onConfirmed = () => {
  http.reset()
  open.value = false
  emit('confirmed')
}

const onSubmit = () =>
  http.post(store.url(), {
    onSuccess: onConfirmed,
  })

const onPasskey = async () => {
  verifying.value = true
  http.clearErrors()

  try {
    await Passkeys.verify({
      routes: {
        options: confirmOptions.url(),
        submit: confirm.url(),
      },
    })

    onConfirmed()
  } catch (error) {
    const message = passkeyErrorMessage(error)

    if (message) {
      http.setError('password', message)
    }
  } finally {
    verifying.value = false
  }
}
</script>

<template>
  <FormModal
    v-model:open="open"
    title="Confirm your identity"
    description="For your security, confirm your password before continuing."
    submit-label="Confirm"
    :processing="http.processing"
    @submit="onSubmit"
  >
    <template #body>
      <div class="flex flex-col gap-4">
        <UFormField
          label="Password"
          required
          :error="http.errors.password"
        >
          <UInput
            v-model="http.password"
            type="password"
            autocomplete="current-password"
            autofocus
            class="w-full"
          />
        </UFormField>

        <UButton
          v-if="hasPasskeys"
          label="Use a passkey instead"
          icon="i-lucide-key-round"
          color="neutral"
          variant="link"
          class="self-start px-0"
          :loading="verifying"
          @click.prevent="onPasskey"
        />
      </div>
    </template>
  </FormModal>
</template>
