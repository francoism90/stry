<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  action: string
}>()

const form = useForm('post', props.action, {
  password: '',
})

const onSubmit = () =>
  form.submit({
    onFinish: () => form.reset('password'),
  })
</script>

<template>
  <Head title="Confirm password" />

  <UPageCard
    title="Confirm password"
    description="This is a secure area. Please confirm your password to continue."
    class="sm:min-w-md"
  >
    <UForm
      :state="form"
      class="flex flex-col gap-4"
      @submit="onSubmit"
    >
      <UFormField
        label="Password"
        required
        :error="form.errors.password"
      >
        <UInput
          v-model="form.password"
          name="password"
          type="password"
          required
          autofocus
          autocomplete="current-password"
          size="lg"
        />
      </UFormField>

      <UButton
        type="submit"
        label="Confirm"
        class="justify-center"
        :loading="form.processing"
      />
    </UForm>
  </UPageCard>
</template>
