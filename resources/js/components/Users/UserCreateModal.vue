<script setup lang="ts">
import { store } from '@/actions/App/Web/Users/Controllers/UserController'
import FormModal from '@/components/Ui/FormModal.vue'
import { useForm } from '@inertiajs/vue3'

withDefaults(
  defineProps<{
    trigger?: boolean
  }>(),
  {
    trigger: true,
  },
)

const open = defineModel<boolean>('open')

const form = useForm(store(), {
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const onSubmit = (close: () => void) =>
  form.submit({
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      close()
    },
  })
</script>

<template>
  <FormModal
    v-model:open="open"
    title="Create User"
    submit-label="Create user"
    :processing="form.processing"
    @submit="onSubmit"
  >
    <template
      v-if="trigger"
      #default
    >
      <slot>
        <UButton
          label="Create user"
          color="neutral"
          variant="link"
          size="sm"
          icon="i-lucide-plus"
          class="px-0"
        />
      </slot>
    </template>

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
          label="Confirm password"
          required
        >
          <UInput
            v-model="form.password_confirmation"
            type="password"
            placeholder="Re-enter password"
          />
        </UFormField>
      </UForm>
    </template>
  </FormModal>
</template>
