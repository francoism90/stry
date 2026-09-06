<script setup lang="ts">
import { store } from '@/actions/App/Web/Videos/Controllers/VideoPlaylistController'
import type { OptionItem, Video } from '@/types'
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
  video: Video
  types?: OptionItem[] | undefined
}>()

const form = useForm({
  type: 'packager',
})

const handle = (close: () => void) =>
  form.post(store.url(props.video.id), {
    preserveScroll: true,
    onSuccess: () => close(),
  })
</script>

<template>
  <UModal
    title="Create playlist"
    :ui="{ footer: 'justify-end' }"
  >
    <slot>
      <UButton
        icon="i-lucide-plus"
        label="Create playlist"
        color="neutral"
        variant="outline"
        size="sm"
      />
    </slot>

    <template #body>
      <UFormField
        label="Type"
        name="type"
        :error="form.errors.type"
      >
        <USelect
          v-model="form.type"
          class="w-full"
          :items="types"
        />
      </UFormField>
    </template>

    <template #footer="{ close }">
      <UButton
        label="Cancel"
        color="neutral"
        variant="soft"
        @click.prevent="close"
      />

      <UButton
        label="Create playlist"
        variant="soft"
        color="primary"
        loading-auto
        :loading="form.processing"
        @click.prevent="handle(close)"
      />
    </template>
  </UModal>
</template>
