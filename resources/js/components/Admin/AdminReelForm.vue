<script setup lang="ts">
import { show, update } from '@/actions/Modules/Web/Settings/Controllers/ReelSettingsController'
import type { ReelSettings } from '@/types'
import { useForm, useHttp } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'

const loaded = ref(false)
const http = useHttp<object, ReelSettings>({})

const codecs = [
  { label: 'H.264', value: 'h264' },
  { label: 'HEVC (H.265)', value: 'hevc' },
  { label: 'AV1', value: 'av1' },
]

const form = useForm(update(), {
  enabled: false,
  width: 1080,
  height: 1920,
  fps: 30,
  codec: 'h264' as ReelSettings['codec'],
  hardware: true,
  quality: 26,
  cuts: 8,
  cut_duration: 4,
  duration: 32,
  scene_threshold: 0.3,
})

onMounted(() =>
  http.get(show.url(), {
    onSuccess: (data) => {
      form.defaults({ ...data })
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
      v-for="i in 10"
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
      title="Reels"
      description="Short vertical highlight videos of the best scenes, shown in the reels feed. Changes apply to new reels; run videos:reels to regenerate existing ones."
      variant="naked"
      orientation="vertical"
      :ui="{
        body: 'flex w-full flex-col gap-3',
      }"
    >
      <template #body>
        <UFormField
          label="Create reels"
          description="Generate a reel when a video is processed."
          name="enabled"
          :error="form.errors.enabled"
          :class="fieldClass"
        >
          <USwitch v-model="form.enabled" />
        </UFormField>

        <USeparator />

        <UFormField
          label="Size"
          description="Width and height in pixels, cropped to fill. Vertical 1080 × 1920 suits phones."
          name="width"
          :error="form.errors.width || form.errors.height"
          :class="fieldClass"
        >
          <div class="flex items-center gap-2">
            <UInputNumber
              v-model="form.width"
              class="w-32"
              :min="240"
              :max="3840"
              :step="2"
              aria-label="Width"
            />
            <span class="text-muted">×</span>
            <UInputNumber
              v-model="form.height"
              class="w-32"
              :min="240"
              :max="3840"
              :step="2"
              aria-label="Height"
            />
          </div>
        </UFormField>

        <USeparator />

        <UFormField
          label="Frame rate"
          description="Frames per second."
          name="fps"
          :error="form.errors.fps"
          :class="fieldClass"
        >
          <UInputNumber
            v-model="form.fps"
            class="w-32"
            :min="10"
            :max="60"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Codec"
          description="H.264 plays everywhere. HEVC is about a third smaller but doesn't play in most versions of Firefox. AV1 is the smallest, but slow to encode on the CPU and needs a recent GPU to encode on it."
          name="codec"
          :error="form.errors.codec"
          :class="fieldClass"
        >
          <USelect
            v-model="form.codec"
            class="w-56"
            :items="codecs"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Encode on GPU"
          description="Use the GPU set in MEDIA_LADDER_HARDWARE, or the CPU when it can't be opened. Turn this off if reels come out corrupted, as HEVC does on some AMD GPUs."
          name="hardware"
          :error="form.errors.hardware"
          :class="fieldClass"
        >
          <USwitch v-model="form.hardware" />
        </UFormField>

        <USeparator />

        <UFormField
          label="Quality"
          description="Lower is sharper and larger. A CRF on the CPU, or the matching quality setting on a GPU; 26 suits reels on phones."
          name="quality"
          :error="form.errors.quality"
          :class="fieldClass"
        >
          <UInputNumber
            v-model="form.quality"
            class="w-32"
            :min="15"
            :max="40"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Duration"
          description="The longest a reel gets, in seconds. Videos up to one and a half times as long are used whole."
          name="duration"
          :error="form.errors.duration"
          :class="fieldClass"
        >
          <UInputNumber
            v-model="form.duration"
            class="w-32"
            :min="5"
            :max="180"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Cuts"
          description="Up to how many scenes a reel joins."
          name="cuts"
          :error="form.errors.cuts"
          :class="fieldClass"
        >
          <UInputNumber
            v-model="form.cuts"
            class="w-32"
            :min="1"
            :max="30"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Cut duration"
          description="Seconds of each scene. Longer cuts are easier to follow, shorter ones feel faster."
          name="cut_duration"
          :error="form.errors.cut_duration"
          :class="fieldClass"
        >
          <UInputNumber
            v-model="form.cut_duration"
            class="w-32"
            :min="1.5"
            :max="30"
            :step="0.5"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Scene sensitivity"
          description="How much the picture has to change to start a new scene. Lower finds more scenes, which helps calm videos."
          name="scene_threshold"
          :error="form.errors.scene_threshold"
          :class="fieldClass"
        >
          <UInputNumber
            v-model="form.scene_threshold"
            class="w-32"
            :min="0.05"
            :max="0.9"
            :step="0.05"
          />
        </UFormField>
      </template>
    </UPageCard>
  </UForm>
</template>
