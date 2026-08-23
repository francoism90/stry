<script setup lang="ts">
import { update } from '@/actions/App/Web/Media/Controllers/MediaController'
import FormModal from '@/components/Ui/FormModal.vue'
import type { Media } from '@/types'
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
  item: Media
}>()

const open = defineModel<boolean>('open')

const form = useForm(update(props.item.id), {
  name: props.item.name,
  custom_properties: props.item.custom_properties ? JSON.stringify(props.item.custom_properties, null, 2) : null,
})

const onSubmit = (close: () => void) =>
  form.submit({
    preserveScroll: true,
    onSuccess: () => close(),
  })
</script>

<template>
  <FormModal
    v-model:open="open"
    :title="`Edit ${item.file_name}`"
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
          />
        </UFormField>

        <UFormField
          label="Metadata"
          :error="form.errors.custom_properties"
        >
          <UTextarea
            v-model="form.custom_properties"
            :model-modifiers="{ nullable: true, string: true, trim: true }"
            :rows="8"
            autoresize
            placeholder="Enter JSON (optional)"
            class="w-full font-mono text-xs"
          />
        </UFormField>
      </UForm>
    </template>
  </FormModal>
</template>
