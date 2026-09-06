<script setup lang="ts">
import { show, update } from '@/actions/App/Web/Settings/Controllers/ChapterSettingsController'
import type { ChapterSettings } from '@/types'
import { useForm, useHttp } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'

const loaded = ref(false)
const http = useHttp<object, ChapterSettings>({})

const form = useForm(update(), {
  patterns: '{}',
  default_type: 'scene',
})

onMounted(() =>
  http.get(show.url(), {
    onSuccess: (data) => {
      form.defaults({
        patterns: JSON.stringify(data.patterns, null, 2),
        default_type: data.default_type,
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
      v-for="i in 4"
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
      title="Chapters"
      description="How chapter labels are classified into a type when none is chosen explicitly."
      variant="naked"
      orientation="vertical"
      :ui="{
        body: 'flex w-full flex-col gap-3',
      }"
    >
      <template #body>
        <UFormField
          label="Default type"
          description="Used when a chapter's label matches none of the patterns below."
          name="default_type"
          :error="form.errors.default_type"
          :class="fieldClass"
        >
          <USelect
            v-model="form.default_type"
            class="w-56"
            :items="[
              { label: 'Intro', value: 'intro' },
              { label: 'Recap', value: 'recap' },
              { label: 'Credits', value: 'credits' },
              { label: 'Scene', value: 'scene' },
              { label: 'Main Event', value: 'main_event' },
            ]"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Patterns"
          description="Maps a chapter type to a regular expression matched against a chapter's label. The first pattern that matches wins."
          name="patterns"
          :error="form.errors.patterns"
        >
          <UTextarea
            v-model="form.patterns"
            :model-modifiers="{ string: true, trim: true }"
            :rows="8"
            autoresize
            placeholder="Enter JSON"
            class="w-full font-mono text-xs"
          />
        </UFormField>
      </template>
    </UPageCard>
  </UForm>
</template>
