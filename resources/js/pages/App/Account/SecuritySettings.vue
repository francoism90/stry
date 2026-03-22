<script setup lang="ts">
import AccountLayout from '@/layouts/App/AccountLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import { update } from '@/routes/user-password'
import { Head } from '@inertiajs/vue3'
import { useForm } from 'laravel-precognition-vue-inertia'

defineOptions({ layout: [DefaultLayout, AccountLayout] })

const form = useForm('put', update.url(), {
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
  <Head title="Security" />

  <UPageBody>
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

          <UFormField
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
  </UPageBody>
</template>
