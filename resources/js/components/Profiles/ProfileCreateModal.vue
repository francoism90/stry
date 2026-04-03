<script setup lang="ts">
import { store } from '@/actions/App/Web/Profiles/Controllers/ProfileController'
import { useForm } from '@inertiajs/vue3'

const form = useForm(store(), {
  name: '',
  is_kids: false,
  is_primary: false,
})

const create = async (close: () => void) =>
  form.submit({
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      close()
    },
  })
</script>

<template>
  <UModal
    title="Create profile"
    :ui="{ footer: 'justify-end' }"
  >
    <slot>
      <UButton
        label="Create profile"
        icon="i-lucide-plus"
        color="neutral"
        variant="soft"
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
            placeholder="Enter profile name"
          />
        </UFormField>

        <UFormField
          label="Kids profile"
          :error="form.errors.is_kids"
        >
          <USwitch v-model="form.is_kids" />
        </UFormField>

        <UFormField
          label="Primary profile"
          :error="form.errors.is_primary"
        >
          <USwitch v-model="form.is_primary" />
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
        label="Create profile"
        color="primary"
        variant="soft"
        loading-auto
        @click.prevent="create(close)"
      />
    </template>
  </UModal>
</template>
