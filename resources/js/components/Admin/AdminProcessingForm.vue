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
  create_renditions: false,
  create_reels: false,
  reel_width: 1080,
  reel_height: 1920,
  reel_fps: 30,
})

onMounted(() =>
  http.get(show.url(), {
    onSuccess: (data) => {
      form.defaults({
        extract_captions: data.extract_captions,
        extract_chapters: data.extract_chapters,
        extract_storyboard: data.extract_storyboard,
        create_renditions: data.create_renditions,
        create_reels: data.create_reels,
        reel_width: data.reel_width,
        reel_height: data.reel_height,
        reel_fps: data.reel_fps,
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
      v-for="i in 7"
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
          label="Extract thumbnails"
          description="Automatically generate seek preview thumbnails for a video."
          name="extract_storyboard"
          :error="form.errors.extract_storyboard"
          :class="fieldClass"
        >
          <USwitch v-model="form.extract_storyboard" />
        </UFormField>

        <USeparator />

        <UFormField
          label="Create renditions"
          description="Offer smaller sizes of a video, encoded while it's watched."
          name="create_renditions"
          :error="form.errors.create_renditions"
          :class="fieldClass"
        >
          <USwitch v-model="form.create_renditions" />
        </UFormField>

        <USeparator />

        <UFormField
          label="Create reels"
          description="Generate a short vertical highlight video of the best scenes, shown in the reels feed."
          name="create_reels"
          :error="form.errors.create_reels"
          :class="fieldClass"
        >
          <USwitch v-model="form.create_reels" />
        </UFormField>

        <USeparator />

        <UFormField
          label="Reel size"
          description="Width and height of new reels in pixels, cropped to fill. Vertical 1080 × 1920 suits phones."
          name="reel_width"
          :error="form.errors.reel_width || form.errors.reel_height"
          :class="fieldClass"
        >
          <div class="flex items-center gap-2">
            <UInputNumber
              v-model="form.reel_width"
              class="w-32"
              :min="240"
              :max="3840"
              :step="2"
              :disabled="!form.create_reels"
              aria-label="Reel width"
            />
            <span class="text-muted">×</span>
            <UInputNumber
              v-model="form.reel_height"
              class="w-32"
              :min="240"
              :max="3840"
              :step="2"
              :disabled="!form.create_reels"
              aria-label="Reel height"
            />
          </div>
        </UFormField>

        <USeparator />

        <UFormField
          label="Reel frame rate"
          description="Frames per second of new reels."
          name="reel_fps"
          :error="form.errors.reel_fps"
          :class="fieldClass"
        >
          <UInputNumber
            v-model="form.reel_fps"
            class="w-32"
            :min="10"
            :max="60"
            :disabled="!form.create_reels"
          />
        </UFormField>
      </template>
    </UPageCard>
  </UForm>
</template>
