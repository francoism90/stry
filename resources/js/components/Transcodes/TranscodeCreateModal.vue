<script setup lang="ts">
import { store } from '@/actions/App/Admin/Transcodes/Controllers/TranscodeController'
import { useForm } from 'laravel-precognition-vue-inertia'

const isOpen = defineModel<boolean>({ default: false })

const form = useForm('post', store.url(), {
  video_id: '',
})

const create = async (close: () => void) => {
  form.submit({
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      close()
    },
  })
}
</script>

<template>
  <UModal
    v-model="isOpen"
    title="Create Transcode"
    :ui="{ footer: 'justify-end' }"
  >
    <UButton
      label="Create transcode"
      color="primary"
      variant="soft"
      icon="i-lucide-plus"
    />

    <template #body>
      <UForm
        :state="form"
        class="flex flex-col gap-4"
      >
        <UFormField
          label="Name"
          required
          :error="form.errors.video_id"
        >
          <UInput
            v-model="form.video_id"
            :model-modifiers="{ string: true, trim: true }"
            autofocus
            placeholder="Enter video ulid"
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
        label="Create transcode"
        variant="soft"
        color="primary"
        loading-auto
        @click.prevent="create(close)"
      />
    </template>
  </UModal>
</template>
