<script setup lang="ts">
import VideoMoveController from '@/actions/Modules/Web/Videos/Controllers/VideoMoveController'
import type { Video, VideoDiskOption } from '@/types'
import { router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps<{
  video: Video
  disks: VideoDiskOption[]
}>()

const disk = ref<string | undefined>()

const items = computed(() =>
  props.disks.map((option) => ({
    label: option.current ? `${option.label} (current)` : option.label,
    value: option.value,
    icon: option.driver === 's3' ? 'i-lucide-cloud' : 'i-lucide-hard-drive',
    disabled: option.current,
  })),
)

const handle = async (close: () => void) =>
  router.post(
    VideoMoveController.url(props.video.id),
    { disk: disk.value },
    {
      preserveScroll: true,
      onSuccess: () => close(),
    },
  )
</script>

<template>
  <UModal
    title="Move video"
    :ui="{ footer: 'justify-end' }"
    @after:leave="disk = undefined"
  >
    <slot>
      <UButton
        icon="i-lucide-hard-drive"
        label="Move"
        color="neutral"
        variant="outline"
        size="sm"
      />
    </slot>

    <template #body>
      <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
          <h3>Which disk should this video be stored on?</h3>
          <p class="text-sm text-neutral-500">
            Its clips and captions are copied to the disk, then removed from the current one. Thumbnails and other
            conversions stay where they are.
          </p>
        </div>

        <USelect
          v-model="disk"
          :items="items"
          placeholder="Choose a disk"
          class="w-full"
        />
      </div>
    </template>

    <template #footer="{ close }">
      <UButton
        label="Cancel"
        color="neutral"
        variant="soft"
        @click.prevent="close"
      />

      <UButton
        label="Move"
        variant="soft"
        color="primary"
        :disabled="!disk"
        loading-auto
        @click.prevent="handle(close)"
      />
    </template>
  </UModal>
</template>
