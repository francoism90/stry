<script setup lang="ts">
import { store } from '@/actions/App/Web/Groups/Controllers/GroupController'
import FormModal from '@/components/Ui/FormModal.vue'
import { useForm } from '@inertiajs/vue3'

const open = defineModel<boolean>('open')

const form = useForm(store(), {
  name: '',
  content: null,
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
    title="Create Collection"
    submit-label="Create collection"
    :processing="form.processing"
    @submit="onSubmit"
  >
    <slot>
      <UButton
        label="Create collection"
        color="neutral"
        variant="link"
        size="sm"
        icon="i-lucide-plus"
        class="px-0"
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
            placeholder="Enter collection name"
          />
        </UFormField>

        <UFormField
          label="Description"
          :error="form.errors.content"
        >
          <UTextarea
            v-model="form.content"
            :rows="3"
            autoresize
            placeholder="Enter description (optional)"
            class="w-full"
          />
        </UFormField>
      </UForm>
    </template>
  </FormModal>
</template>
