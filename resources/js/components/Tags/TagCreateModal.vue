<script setup lang="ts">
import { store } from '@/actions/App/Web/Tags/Controllers/TagController'
import type { SelectMenuItem } from '@nuxt/ui'
import { useForm } from 'laravel-precognition-vue-inertia'

defineProps<{
  types: SelectMenuItem[] | undefined
}>()

const form = useForm('post', store.url(), {
  name: '',
  type: null,
  description: null,
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
    title="Create Tag"
    :ui="{ footer: 'justify-end' }"
  >
    <slot>
      <UButton
        label="Create tag"
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
            placeholder="Enter tag name"
          />
        </UFormField>

        <UFormField
          label="Type"
          required
          :error="form.errors.type"
        >
          <USelectMenu
            v-model="form.type"
            value-key="value"
            :items="types"
            placeholder="Select a type"
            class="w-full"
          />
        </UFormField>

        <UFormField
          label="Description"
          :error="form.errors.description"
        >
          <UTextarea
            v-model="form.description"
            :rows="3"
            autoresize
            placeholder="Enter markdown (optional)"
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
        label="Create tag"
        variant="soft"
        color="primary"
        loading-auto
        @click.prevent="create(close)"
      />
    </template>
  </UModal>
</template>
