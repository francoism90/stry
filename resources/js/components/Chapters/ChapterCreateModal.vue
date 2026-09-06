<script setup lang="ts">
import { store } from '@/actions/App/Web/Videos/Controllers/VideoChapterController'
import type { Video } from '@/types'
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
  video: Video
}>()

const form = useForm({
  label: '',
  type: undefined as string | undefined,
  start_time: '' as number | string,
  end_time: '' as number | string,
})

const handle = (close: () => void) =>
  form.post(store.url(props.video.id), {
    preserveScroll: true,
    onSuccess: () => close(),
  })
</script>

<template>
  <UModal
    title="Create chapter"
    :ui="{ footer: 'justify-end' }"
  >
    <slot>
      <UButton
        icon="i-lucide-plus"
        label="Create chapter"
        color="neutral"
        variant="outline"
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
            placeholder="Introduction"
          />
        </UFormField>

        <UFormField
          label="Type"
          :error="form.errors.type"
          description="Leave unset to classify automatically from the label."
        >
          <USelect
            v-model="form.type"
            class="w-full"
            placeholder="Auto"
            :items="[
              { label: 'Intro', value: 'intro' },
              { label: 'Recap', value: 'recap' },
              { label: 'Credits', value: 'credits' },
              { label: 'Scene', value: 'scene' },
            ]"
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

    <template #footer="{ close }">
      <UButton
        label="Cancel"
        color="neutral"
        variant="soft"
        @click.prevent="close"
      />

      <UButton
        label="Create chapter"
        variant="soft"
        color="primary"
        loading-auto
        :loading="form.processing"
        @click.prevent="handle(close)"
      />
    </template>
  </UModal>
</template>
