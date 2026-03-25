<script setup lang="ts">
import { store } from '@/actions/App/Web/Groups/Controllers/GroupController'
import { useForm } from '@inertiajs/vue3'

const form = useForm(store(), {
  name: '',
  content: null,
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
    title="Create Collection"
    :ui="{ footer: 'justify-end' }"
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

    <template #footer="{ close }">
      <UButton
        label="Cancel"
        color="neutral"
        variant="soft"
        @click.prevent="close"
      />

      <UButton
        label="Create collection"
        variant="soft"
        color="primary"
        loading-auto
        @click.prevent="create(close)"
      />
    </template>
  </UModal>
</template>
