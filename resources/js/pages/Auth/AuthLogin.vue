<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
  action: string
  status?: string
}>()

defineOptions({ layout: AuthLayout })

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
</script>

<template>
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
          autocomplete="email"
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
    </UForm>
  </UPageCard>
</template>
