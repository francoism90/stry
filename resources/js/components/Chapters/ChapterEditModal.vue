<script setup lang="ts">
import { update } from '@/actions/App/Web/Videos/Controllers/VideoChapterController'
import FormModal from '@/components/Ui/FormModal.vue'
import type { Chapter, OptionItem, Video } from '@/types'
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
  video: Video
  item: Chapter
  types?: OptionItem[] | undefined
}>()

const open = defineModel<boolean>('open')

const form = useForm(update([props.video.id, props.item.id]), {
  label: props.item.label,
  type: props.item.type,
  start_time: props.item.start_time,
  end_time: props.item.end_time,
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
    :title="`Edit ${item.label}`"
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
        class="flex flex-col gap-3"
      >
        <UFormField
          label="Label"
          required
          :error="form.errors.label"
        >
          <UInput
            v-model="form.label"
            :model-modifiers="{ string: true, trim: true }"
            autofocus
          />
        </UFormField>

        <UFormField
          label="Type"
          :error="form.errors.type"
        >
          <USelect
            v-model="form.type"
            class="w-full"
            :items="types"
          />
        </UFormField>

        <div class="grid grid-cols-2 gap-3">
          <UFormField
            label="Start (seconds)"
            required
            :error="form.errors.start_time"
          >
            <UInput
              v-model="form.start_time"
              :model-modifiers="{ number: true }"
              type="number"
              step="0.01"
              min="0"
            />
          </UFormField>

          <UFormField
            label="End (seconds)"
            required
            :error="form.errors.end_time"
          >
            <UInput
              v-model="form.end_time"
              :model-modifiers="{ number: true }"
              type="number"
              step="0.01"
              min="0"
            />
          </UFormField>
        </div>
      </UForm>
    </template>
  </FormModal>
</template>
