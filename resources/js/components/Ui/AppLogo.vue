<script setup lang="ts">
defineProps<{
  location?: string
}>()
</script>

<template>
  <ULink
    class="font-serif text-sm font-bold text-neutral-400 uppercase"
    :to="location || '/'"
  >
    <span>stry</span>
  </ULink>
</template>
