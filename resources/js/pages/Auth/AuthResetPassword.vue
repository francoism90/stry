<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  action: string
  token: string
  email: string
}>()

const form = useForm('post', props.action, {
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
})

const onSubmit = () =>
  form.submit({
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
</script>

<template>
  <Head title="Reset password" />

  <UPageCard
    title="Reset password"
    description="Choose a new password for your account."
    class="sm:min-w-md"
  >
    <UForm
      :state="form"
      class="flex flex-col gap-4"
      @submit="onSubmit"
    >
      <UFormField
        label="Email"
        required
        :error="form.errors.email"
      >
        <UInput
          v-model="form.email"
          name="email"
          type="email"
          required
          readonly
          autocomplete="email"
          size="lg"
        />
      </UFormField>

      <UFormField
        label="New password"
        required
        :error="form.errors.password"
      >
        <UInput
          v-model="form.password"
          name="password"
          type="password"
          required
          autofocus
          autocomplete="new-password"
          size="lg"
        />
      </UFormField>

      <UFormField
        label="Confirm password"
        required
        :error="form.errors.password_confirmation"
      >
        <UInput
          v-model="form.password_confirmation"
          name="password_confirmation"
          type="password"
          required
          autocomplete="new-password"
          size="lg"
        />
      </UFormField>

      <UButton
        type="submit"
        label="Reset password"
        class="justify-center"
        :loading="form.processing"
      />
    </UForm>
  </UPageCard>
</template>
