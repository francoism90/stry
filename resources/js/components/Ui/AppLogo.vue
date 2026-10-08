<script setup lang="ts">
import { useAppearance } from '@/composables/appearance'

defineProps<{
  location?: string
}>()

const { app } = useAppearance()
</script>

<template>
  <ULink
    class="flex w-full items-center gap-2.5 text-lg font-bold tracking-tight text-highlighted"
    :to="location || '/'"
  >
    <span
      class="grid size-7 shrink-0 place-items-center rounded-[9px] bg-(image:--logo-gradient) shadow-[inset_0_1px_0_rgb(255_255_255/0.35)]"
      aria-hidden="true"
    >
      <svg
        viewBox="0 0 24 24"
        class="ms-px size-4 fill-white stroke-white stroke-3"
        stroke-linejoin="round"
      >
        <path d="M8 5.5v13l10.5-6.5z" />
      </svg>
    </span>
    <span class="inline">{{ app }}</span>
  </ULink>
</template>
