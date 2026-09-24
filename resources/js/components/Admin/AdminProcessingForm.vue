<script setup lang="ts">
import { show, update } from '@/actions/Modules/Web/Settings/Controllers/ProcessingSettingsController'
import type { ProcessingSettings } from '@/types'
import { useForm, useHttp } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'

const loaded = ref(false)
const http = useHttp<object, ProcessingSettings>({})

const form = useForm(update(), {
  extract_captions: true,
  extract_chapters: true,
  extract_storyboard: true,
})

onMounted(() =>
  http.get(show.url(), {
    onSuccess: (data) => {
      form.defaults({
        extract_captions: data.extract_captions,
        extract_chapters: data.extract_chapters,
        extract_storyboard: data.extract_storyboard,
      })
      form.reset()
      loaded.value = true
    },
  }),
)

const onSubmit = () => {
  if (!loaded.value) return

  form.submit({
    preserveScroll: true,
    preserveState: true,
  })
}

defineExpose({
  submit: onSubmit,
  get processing() {
    return form.processing
  },
  get recentlySuccessful() {
    return form.recentlySuccessful
  },
})

const fieldClass = 'flex max-sm:flex-col justify-between items-start gap-4'
</script>

<template>
  <div
    v-if="!loaded"
    class="flex flex-col gap-3"
  >
    <USkeleton
      v-for="i in 3"
      :key="i"
      class="h-10 w-full rounded-md"
    />
  </div>

  <UForm
    v-else
    :state="form"
    class="flex flex-col gap-4"
    loading-auto
    @submit="onSubmit"
  >
    <UPageCard
      title="Processing"
      description="Automatic extraction that runs when a video is processed. Disabling these can help on low-performance setups."
      variant="naked"
      orientation="vertical"
      :ui="{
        body: 'flex w-full flex-col gap-3',
      }"
    >
      <template #body>
        <UFormField
          label="Extract captions"
          description="Automatically extract captions from a video's clips."
          name="extract_captions"
          :error="form.errors.extract_captions"
          :class="fieldClass"
        >
          <USwitch v-model="form.extract_captions" />
        </UFormField>

        <USeparator />

        <UFormField
          label="Extract chapters"
          description="Automatically detect and create chapters from a video's clips."
          name="extract_chapters"
          :error="form.errors.extract_chapters"
          :class="fieldClass"
        >
          <USwitch v-model="form.extract_chapters" />
        </UFormField>

        <USeparator />

        <UFormField
          label="Extract storyboard"
          description="Automatically generate a storyboard preview sprite for a video."
          name="extract_storyboard"
          :error="form.errors.extract_storyboard"
          :class="fieldClass"
        >
          <USwitch v-model="form.extract_storyboard" />
        </UFormField>
      </template>
    </UPageCard>
  </UForm>
</template>
