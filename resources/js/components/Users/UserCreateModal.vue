<script setup lang="ts">
import { store } from '@/actions/App/Admin/Users/Controllers/UserController'
import { useForm } from 'laravel-precognition-vue-inertia'

const form = useForm('post', store.url(), {
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const handle = async (close: () => void) => {
  form.submit({
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      close()
    },
  })
}
</script>

<template>
  <UModal
    title="Create User"
    :ui="{ footer: 'justify-end' }"
  >
    <slot>
      <UButton
        label="Create user"
        color="primary"
        variant="soft"
        icon="i-lucide-plus"
      />
    </slot>

    <template #body>
      <UForm
        :state="form"
        class="flex flex-col gap-4"
      >
        <UFormField
          label="Name"
          required
          :error="form.errors.name"
        >
          <UInput
            v-model="form.name"
            :model-modifiers="{ string: true, trim: true }"
            autofocus
            autocapitalize="words"
            placeholder="Enter user name"
          />
        </UFormField>

        <UFormField
          label="Email"
          required
          :error="form.errors.email"
        >
          <UInput
            v-model="form.email"
            :model-modifiers="{ string: true, trim: true }"
            type="email"
            placeholder="Enter email address"
          />
        </UFormField>

        <UFormField
          label="Password"
          required
          :error="form.errors.password"
        >
          <UInput
            v-model="form.password"
            type="password"
            placeholder="Enter password"
          />
        </UFormField>

        <UFormField
          label="Confirm Password"
          required
          :error="form.errors.password_confirmation"
        >
          <UInput
            v-model="form.password_confirmation"
            type="password"
            placeholder="Confirm password"
          />
        </UFormField>
      </UForm>
    </template>

    <template #footer="{ close }">
      <UButton
        label="Cancel"
        color="neutral"
        variant="soft"
        @click.prevent="close"
      />

      <UButton
        label="Create user"
        variant="soft"
        color="primary"
        loading-auto
        @click.prevent="handle(close)"
      />
    </template>
  </UModal>
</template>
