<script setup lang="ts">
import { useAppearance } from '@/composables/appearance'

defineProps<{
  location?: string
}>()

const { app } = useAppearance()
</script>

<template>
  <ULink
    class="flex items-center gap-1.5 text-sm font-bold tracking-wider text-neutral-300"
    :to="location || '/'"
  >
    <UIcon
      name="i-lucide-circle-play"
      class="size-6 shrink-0 text-primary"
    />
    <span class="inline">{{ app }}</span>
  </ULink>
</template>
