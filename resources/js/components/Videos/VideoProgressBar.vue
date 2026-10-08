<script setup lang="ts">
defineProps<{
  fraction: number
}>()
</script>

<template>
  <span
    class="pointer-events-none absolute inset-x-0 bottom-0 h-1 bg-white/30"
    aria-hidden="true"
  >
    <span
      class="block h-full bg-primary"
      :style="{ width: `${fraction * 100}%` }"
    />
  </span>
</template>
