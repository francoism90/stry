<script setup lang="ts">
import { show, update } from '@/actions/Modules/Web/Settings/Controllers/PlaybackSettingsController'
import { useLocale } from '@/composables/locale'
import type { PlaybackSettings } from '@/types'
import { useForm, useHttp } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'

const loaded = ref(false)
const http = useHttp<object, PlaybackSettings>({})
const { languages } = useLocale()

const form = useForm<PlaybackSettings>(update(), {
  text_language: 'en',
  encryption: false,
  refresh_before: 0,
})

onMounted(() =>
  http.get(show.url(), {
    onSuccess: (data) => {
      form.defaults(data)
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
      title="Playback"
      description="How videos are streamed from their clips."
      variant="naked"
      orientation="vertical"
      :ui="{
        body: 'flex w-full flex-col gap-3',
      }"
    >
      <template #body>
        <UFormField
          label="Text language"
          description="Subtitle language for captions that don't have one."
          name="text_language"
          :error="form.errors.text_language"
          :class="fieldClass"
        >
          <USelect
            v-model="form.text_language"
            class="w-56"
            :items="languages"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Encryption"
          description="Encrypt segments per request with Clear Key (DASH) or SAMPLE-AES-CTR (HLS)."
          name="encryption"
          :error="form.errors.encryption"
          :class="fieldClass"
        >
          <USwitch v-model="form.encryption" />
        </UFormField>

        <USeparator />

        <UFormField
          label="Refresh before"
          description="How many seconds before the signed stream URLs expire the player fetches new ones."
          name="refresh_before"
          :error="form.errors.refresh_before"
          :class="fieldClass"
        >
          <UInputNumber
            v-model="form.refresh_before"
            class="w-56"
            :min="0"
          />
        </UFormField>
      </template>
    </UPageCard>
  </UForm>
</template>
