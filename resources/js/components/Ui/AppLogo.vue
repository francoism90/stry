<script setup lang="ts">
defineProps<{
  location?: string
}>()
</script>

<template>
  <ULink
    class="flex items-center gap-1.5 text-sm font-bold tracking-widest text-white uppercase"
    :to="location || '/'"
  >
    <UIcon
      name="i-lucide-circle-play"
      class="size-6 shrink-0 text-primary"
    />
    <span class="hidden sm:inline">stry</span>
  </ULink>
</template>
