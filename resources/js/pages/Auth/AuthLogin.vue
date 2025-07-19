<script setup lang="ts">
import AuthLayout from '@/layouts/MinimalLayout.vue'
import { useForm } from '@inertiajs/vue3'

defineOptions({
  layout: AuthLayout,
})

const props = defineProps<{
  action: string
  status?: string
}>()

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = async () => {
  form
    .transform((data) => ({
      ...data,
      remember: form.remember ? 'on' : '',
    }))
    .post(props.action, {
      onFinish: () => form.reset('password'),
    })
}
</script>

<template>
  <UCard
    variant="soft"
    class="w-full"
  >
    <template #header>
      <h2 class="text-md font-semibold">Login to your account</h2>
    </template>

    <UForm
      :state="form"
      @submit.prevent="submit"
      class="flex flex-col gap-6"
    >
      <UFormField
        label="Your Email"
        name="email"
        required
        :error="form.errors.email"
      >
        <UInput
          type="email"
          required
          autofocus
          autocomplete="email"
          placeholder="email@example.com"
          size="lg"
          v-model="form.email"
        />
      </UFormField>

      <UFormField
        label="Your Password"
        name="password"
        required
        :error="form.errors.password"
      >
        <UInput
          id="password"
          type="password"
          required
          autocomplete="current-password"
          placeholder="Password"
          size="lg"
          v-model="form.password"
        />
      </UFormField>

      <UButton
        type="submit"
        color="primary"
        class="w-fit"
        variant="solid"
        loading-auto
      >
        Submit
      </UButton>
    </UForm>
  </UCard>
</template>
