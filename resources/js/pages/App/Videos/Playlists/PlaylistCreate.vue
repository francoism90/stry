<script setup lang="ts">
import { store } from '@/actions/App/Web/Videos/Controllers/VideoPlaylistController'
import VideoLayout from '@/layouts/App/VideoLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { Video } from '@/types'
import { Head, useForm } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

const props = defineProps<{
  video: Video
  types: SelectMenuItem[]
}>()

defineOptions({ layout: [DefaultLayout, VideoLayout] })

const form = useForm(store(props.video.id), {
  type: null as string | null,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
  })
</script>

<template>
  <Head :title="`${video.title} - Create Playlist`" />

  <UPageBody>
    <UForm
      :state="form"
      class="flex flex-col py-3"
      loading-auto
      @submit="onSubmit"
    >
      <UPageCard
        variant="subtle"
        orientation="vertical"
        :ui="{
          body: 'flex w-full flex-col gap-3',
        }"
      >
        <template #body>
          <UFormField
            label="Type"
            required
            :error="form.errors.type"
          >
            <USelect
              v-model="form.type"
              :items="types"
              label-key="label"
              value-key="value"
              placeholder="Select a type"
              class="w-full"
            />
          </UFormField>
        </template>

        <template #footer>
          <UButton
            type="submit"
            label="Create playlist"
            icon="i-lucide-list-video"
            :loading="form.processing"
          />
        </template>
      </UPageCard>
    </UForm>
  </UPageBody>
</template>
