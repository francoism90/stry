<script setup lang="ts">
import { destroy } from '@/actions/Modules/Web/Groups/Controllers/GroupController'
import type { Group } from '@/types'
import { router } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps<{
  item: Group
}>()

const videoCount = computed(() => props.item.videos ?? 0)

const keptVideosLabel = computed(() => {
  if (videoCount.value === 0) {
    return ''
  }

  return videoCount.value === 1
    ? 'Its video stays in your library.'
    : `Its ${Intl.NumberFormat().format(videoCount.value)} videos stay in your library.`
})

const handle = async () => router.delete(destroy.url(props.item.id))
</script>

<template>
  <UModal
    title="Delete this collection?"
    :ui="{ footer: 'justify-end' }"
  >
    <slot>
      <UButton
        icon="i-lucide-trash"
        aria-label="Delete collection"
        color="error"
        variant="ghost"
        size="sm"
      />
    </slot>

    <template #body>
      <p class="text-sm text-muted">
        <span class="text-highlighted capitalize">{{ item.title ?? item.id }}</span>
        will be removed. {{ keptVideosLabel }}
      </p>
    </template>

    <template #footer="{ close }">
      <UButton
        label="Cancel"
        color="neutral"
        variant="soft"
        @click.prevent="close"
      />

      <UButton
        label="Delete collection"
        variant="solid"
        color="error"
        loading-auto
        @click.prevent="handle"
      />
    </template>
  </UModal>
</template>
