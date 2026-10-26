<script setup lang="ts">
import { usePasskeyLogin } from '@/composables/passkeys'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  action: string
  status?: string
}>()

const form = useForm('post', props.action, {
  email: '',
  password: '',
  remember: false,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    onSuccess: () => form.reset('password'),
  })

const {
  supported: passkeySupported,
  verifying: verifyingPasskey,
  error: passkeyError,
  verify: verifyPasskey,
} = usePasskeyLogin(() => form.remember)
</script>

<template>
  <Head title="Log in" />

  <UPageCard
    title="Login"
    description="Enter your credentials to access your account."
    class="sm:min-w-md"
  >
    <UForm
      :state="form"
      @submit="onSubmit"
      class="flex flex-col gap-4"
    >
      <UFormField
        label="Your Email"
        required
        :error="form.errors.email"
      >
        <UInput
          v-model="form.email"
          :model-modifiers="{ string: true, trim: true }"
          type="email"
          required
          autofocus
          autocomplete="email webauthn"
          placeholder="email@example.com"
          size="lg"
        />
      </UFormField>

      <UFormField
        label="Your Password"
        required
        :error="form.errors.password"
      >
        <UInput
          v-model="form.password"
          :model-modifiers="{ string: true, trim: true }"
          type="password"
          required
          autocomplete="current-password"
          placeholder="Password"
          size="lg"
        />
      </UFormField>

      <UFormField
        name="remember"
        required
        :error="form.errors.remember"
      >
        <UCheckbox
          v-model="form.remember"
          :model-modifiers="{ nullable: true }"
          id="remember"
          label="Remember me"
        />
      </UFormField>

      <UButton
        type="submit"
        label="Continue"
        color="primary"
        class="justify-center"
        variant="solid"
        loading-auto
      />

      <template v-if="passkeySupported">
        <USeparator label="or" />

        <UButton
          label="Sign in with a passkey"
          icon="i-lucide-key-round"
          color="neutral"
          variant="soft"
          class="justify-center"
          :loading="verifyingPasskey"
          @click.prevent="verifyPasskey"
        />

        <p
          v-if="passkeyError"
          class="text-center text-sm text-error"
        >
          {{ passkeyError }}
        </p>
      </template>
    </UForm>
  </UPageCard>
</template>
