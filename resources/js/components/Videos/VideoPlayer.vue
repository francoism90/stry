<script setup lang="ts">
import { useShaka } from '@/composables/shaka'
import { router } from '@inertiajs/vue3'
import type { ButtonProps } from '@nuxt/ui'
import { onMounted, ref } from 'vue'

const ui = ref<HTMLDivElement | undefined>()
const el = ref<HTMLMediaElement | undefined>()

const { initialize, ready, error } = useShaka(ui, el)

const actions = ref<ButtonProps[]>([
  {
    icon: 'i-lucide-refresh-cw',
    label: 'Refresh',
    color: 'neutral',
    size: 'sm',
    variant: 'subtle',
    onClick: () => router.reload({ only: ['playlist, progress'] }),
  },
  {
    icon: 'i-lucide-flag',
    label: 'Report',
    color: 'neutral',
    size: 'sm',
    variant: 'subtle',
  },
])

onMounted(() => initialize())
</script>

<template>
  <figure>
    <UEmpty
      v-if="!ready && !error"
      title="Preparing your video..."
      description="This will refresh automatically once your video is ready."
      icon="i-lucide-hard-drive-download"
      :actions="actions"
    />

    <UEmpty
      v-if="error"
      title="Playback Error"
      :description="error.message || 'An error occurred during video playback.'"
      icon="i-lucide-alert-circle"
      :actions="actions"
    />

    <div
      ref="ui"
      v-show="ready && !error"
      class="fullscreen:aspect-auto fullscreen:max-h-none fullscreen:rounded-none aspect-video max-h-[35dvh] w-full overflow-hidden rounded-lg md:max-h-[50dvh] lg:max-h-[60dvh]"
    >
      <video
        ref="el"
        class="size-full bg-black"
        preload="metadata"
        crossorigin="anonymous"
        playsinline
        autoplay
      />
    </div>
  </figure>
</template>
