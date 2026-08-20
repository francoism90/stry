<script setup lang="ts">
import { update } from '@/routes/user-password'
import { useForm } from '@inertiajs/vue3'

const form = useForm(update(), {
  current_password: '',
  password: '',
  password_confirmation: '',
})

const onSubmit = () =>
  form.submit({
    preserveScroll: true,
    onSuccess: () => form.reset(),
  })
</script>

<template>
  <UForm
    :state="form"
    class="flex flex-col gap-6 py-3"
    loading-auto
    @submit="onSubmit"
  >
    <UPageCard
      title="Change Password"
      description="Update your account password."
      variant="subtle"
      orientation="vertical"
      :ui="{
        body: 'flex w-full flex-col gap-4',
      }"
    >
      <template #body>
        <div class="flex flex-col gap-1">
          <p class="text-sm font-semibold text-highlighted">Change password</p>
          <p class="text-sm text-muted">Choose a strong password and keep your account secure.</p>
        </div>

        <UFormField
          label="Current password"
          required
          :error="form.errors.current_password"
        >
          <UInput
            v-model="form.current_password"
            type="password"
            autocomplete="current-password"
          />
        </UFormField>

        <USeparator />

        <div class="grid grid-cols-12 gap-4">
          <UFormField
            class="col-span-12 md:col-span-6"
            label="New password"
            required
            :error="form.errors.password"
          >
            <UInput
              v-model="form.password"
              type="password"
              autocomplete="new-password"
              @change="form.validate('password')"
            />
          </UFormField>

          <UFormField
            class="col-span-12 md:col-span-6"
            label="Confirm new password"
            required
            :error="form.errors.password_confirmation"
          >
            <UInput
              v-model="form.password_confirmation"
              type="password"
              autocomplete="new-password"
            />
          </UFormField>
        </div>
      </template>

      <template #footer>
        <UButton
          label="Update password"
          type="submit"
          color="primary"
          variant="soft"
          loading-auto
        />
      </template>
    </UPageCard>
  </UForm>
</template>
