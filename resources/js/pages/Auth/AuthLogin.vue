<script setup lang="ts">
import AuthLayout from '@/layouts/MinimalLayout.vue'
import { useForm } from 'laravel-precognition-vue-inertia'

defineOptions({
  layout: AuthLayout,
})

const props = defineProps<{
  action: string
  status?: string
}>()

const form = useForm('post', props.action, {
  email: '',
  password: '',
  remember: false,
})

const onSubmit = async () =>
  form.submit({
    preserveState: true,
    onSuccess: () => form.reset('password'),
  })
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
      @submit="onSubmit"
      class="flex flex-col gap-6"
    >
      <UFormField
        label="Your Email"
        name="email"
        required
        :error="form.errors.email"
      >
        <UInput
          v-model="form.email"
          :model-modifiers="{ nullable: true, string: true, trim: true }"
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
        name="password"
        required
        :error="form.errors.password"
      >
        <UInput
          v-model="form.password"
          :model-modifiers="{ nullable: true, string: true, trim: true }"
          id="password"
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
