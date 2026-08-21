<script setup lang="ts">
import { update } from '@/actions/App/Web/Profiles/Controllers/ProfileController'
import FormModal from '@/components/Ui/FormModal.vue'
import type { Profile } from '@/types'
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
  item: Profile
}>()

const form = useForm(update(props.item.id), {
  name: props.item.name,
  is_kids: props.item.is_kids,
  is_primary: props.item.is_primary,
})

const onSubmit = (close: () => void) =>
  form.submit({
    preserveScroll: true,
    onSuccess: () => close(),
  })
</script>

<template>
  <FormModal
    :title="item.name"
    submit-label="Save profile"
    :processing="form.processing"
    @submit="onSubmit"
  >
    <slot>
      <UButton
        icon="i-lucide-pencil"
        color="neutral"
        variant="ghost"
        size="sm"
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
  </FormModal>
</template>
