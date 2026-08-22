<script setup lang="ts">
import { store } from '@/actions/App/Web/Profiles/Controllers/ProfileController'
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
  is_kids: false,
  is_primary: false,
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
    title="Create Profile"
    submit-label="Create profile"
    :processing="form.processing"
    @submit="onSubmit"
  >
    <template
      v-if="trigger"
      #default
    >
      <slot>
        <UButton
          label="Create profile"
          icon="i-lucide-plus"
          color="neutral"
          variant="link"
          size="sm"
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
  </FormModal>
</template>
