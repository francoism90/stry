<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  action: string
  loginUrl: string
  status?: string
}>()

const form = useForm('post', props.action, {
  email: '',
})

const onSubmit = () => form.submit()
</script>

<template>
  <Head title="Forgot password" />

  <UPageCard
    title="Forgot password"
    description="Enter your email and we'll send you a link to reset your password."
    class="sm:min-w-md"
  >
    <UAlert
      v-if="status"
      :title="status"
      color="success"
      variant="subtle"
    />

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
          :model-modifiers="{ trim: true }"
          type="email"
          required
          autofocus
          autocomplete="email"
          placeholder="email@example.com"
          size="lg"
        />
      </UFormField>

      <UButton
        type="submit"
        label="Email reset link"
        class="justify-center"
        :loading="form.processing"
      />
    </UForm>

    <template #footer>
      <p class="text-center text-sm text-muted">
        <ULink :to="loginUrl">Back to log in</ULink>
      </p>
    </template>
  </UPageCard>
</template>
